<?php

namespace Lsv\Vertification\Controller;

use Lsv\Vertification\Event\AlreadyValidatedEvent;
use Lsv\Vertification\Event\AwaitingValidationEvent;
use Lsv\Vertification\Event\FinishedRequestEvent;
use Lsv\Vertification\Event\FinishedResponseEvent;
use Lsv\Vertification\Handler\TypeHandler;
use Lsv\Vertification\TypeInterface;
use Lsv\Vertification\TypeResponseInterface;
use Lsv\Vertification\ValidationUserInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class DefaultController
{
    /**
     * @var string
     */
    private $alreadyValidatedRouteName;

    /**
     * @var string
     */
    private $awaitingValidationRouteName;

    /**
     * @var string
     */
    private $responseValidatedRouteName;

    /**
     * @var TypeHandler
     */
    private $handler;

    /**
     * @var array
     */
    private $options;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var RouterInterface
     */
    private $router;
    /**
     * @var TokenStorageInterface
     */
    private $storage;

    public function __construct(
        string $alreadyValidatedRouteName,
        string $awaitingValidationRouteName,
        string $responseValidatedRouteName,
        array $options,
        EventDispatcherInterface $dispatcher,
        TypeHandler $handler,
        RouterInterface $router,
        TokenStorageInterface $storage
    ) {
        $this->handler = $handler;
        $this->options = $options;
        $this->dispatcher = $dispatcher;
        $this->alreadyValidatedRouteName = $alreadyValidatedRouteName;
        $this->awaitingValidationRouteName = $awaitingValidationRouteName;
        $this->responseValidatedRouteName = $responseValidatedRouteName;
        $this->router = $router;
        $this->storage = $storage;
    }

    /**
     * @param Request $request
     * @param string  $type
     *
     * @return Response
     */
    public function request(Request $request, $type): Response
    {
        $typeInterface = $this->handler->getType($type, $this->options);
        if ($isValidated = $this->isValidated($typeInterface, $request)) {
            return $isValidated;
        }

        if ($isAwaiting = $this->isAwaitingValidation($typeInterface, $request)) {
            return $isAwaiting;
        }

        $form = $typeInterface->requestForm($this->getUser(), $request, [
            'method' => 'POST',
            'action' => $this->router->generate('lsv_vertification_request', [
                'type' => $type,
            ]),
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $typeInterface->handleRequestForm($this->getUser(), $form, $request);
            if ($form->isValid()) {
                if ($typeInterface instanceof TypeResponseInterface) {
                    return $this->createFinishRequest($typeInterface, $form, $request);
                }

                return $this->createFinishResponse($typeInterface, $form, $request);
            }
        }

        return $typeInterface->requestTemplate($this->getUser(), $form, $typeInterface);
    }

    /**
     * @param Request $request
     * @param string  $type
     *
     * @return Response
     */
    public function response(Request $request, $type): Response
    {
        try {
            $typeInterface = $this->handler->getType($type, $this->options);
        } catch (\InvalidArgumentException $exception) {
            return $this->redirectToRoute('lsv_vertification_request');
        }

        if ($isValidated = $this->isValidated($typeInterface, $request)) {
            return $isValidated;
        }

        if ($isAwaiting = $this->isAwaitingValidation($typeInterface, $request)) {
            return $isAwaiting;
        }

        if ($typeInterface instanceof TypeResponseInterface) {
            $form = $typeInterface->responseForm($this->getUser(), $request, [
                'method' => 'POST',
                'action' => $this->router->generate('lsv_vertification_response', [
                    'type' => $type,
                ]),
            ]);
            $form->handleRequest($request);
            if ($form->isSubmitted()) {
                $typeInterface->handleResponseForm($this->getUser(), $form, $request);
                if ($form->isValid()) {
                    return $this->createFinishResponse($typeInterface, $form, $request);
                }
            }

            return $typeInterface->responseTemplate($this->getUser(), $form, $typeInterface);
        }

        return $this->redirectToRoute('lsv_vertification_request');
    }

    /**
     * @return ValidationUserInterface
     */
    protected function getUser(): ValidationUserInterface
    {
        $token = $this->storage->getToken();
        if (null === $token || !method_exists($token, 'getUser')) {
            throw new \InvalidArgumentException('User token needs to be set');
        }

        if (!\is_object($user = $token->getUser())) {
            throw new \InvalidArgumentException('User token needs to be set');
        }

        if (!$user instanceof ValidationUserInterface) {
            throw new \InvalidArgumentException('User does not supports validation');
        }

        return $user;
    }

    /**
     * @param TypeInterface $typeInterface
     * @param Request       $request
     *
     * @return RedirectResponse|false
     */
    protected function isValidated(TypeInterface $typeInterface, Request $request)
    {
        if ($typeInterface->isValidated($this->getUser())) {
            $response = $this->redirectToRoute($this->alreadyValidatedRouteName);
            $this->dispatcher->dispatch(
                AlreadyValidatedEvent::NAME,
                new AlreadyValidatedEvent($this->getUser(), $typeInterface, $request, $response)
            );

            return $response;
        }

        return false;
    }

    /**
     * @param TypeInterface $typeInterface
     * @param Request       $request
     *
     * @return RedirectResponse|false
     */
    protected function isAwaitingValidation(TypeInterface $typeInterface, Request $request)
    {
        if ($typeInterface->awaitingValidated($this->getUser())) {
            $response = $this->redirectToRoute($this->awaitingValidationRouteName);
            $this->dispatcher->dispatch(
                AwaitingValidationEvent::NAME,
                new AwaitingValidationEvent($this->getUser(), $typeInterface, $request, $response)
            );

            return $response;
        }

        return false;
    }

    /**
     * @param TypeInterface $typeInterface
     * @param FormInterface $form
     * @param Request       $request
     *
     * @return RedirectResponse
     */
    protected function createFinishRequest(TypeInterface $typeInterface, FormInterface $form, Request $request): RedirectResponse
    {
        $response = $this->redirectToRoute('lsv_vertification_response', [
            'type' => $typeInterface->getKey(),
        ]);
        $this->dispatcher->dispatch(
            FinishedRequestEvent::NAME,
            new FinishedRequestEvent(
                $typeInterface,
                $form,
                $this->getUser(),
                $request,
                $response
            )
        );

        return $response;
    }

    /**
     * @param TypeInterface $typeInterface
     * @param FormInterface $form
     * @param Request       $request
     *
     * @return RedirectResponse
     */
    protected function createFinishResponse(TypeInterface $typeInterface, FormInterface $form, Request $request): RedirectResponse
    {
        $response = $this->redirectToRoute($this->responseValidatedRouteName);
        $this->dispatcher->dispatch(
            FinishedResponseEvent::NAME,
            new FinishedResponseEvent(
                $typeInterface,
                $form,
                $this->getUser(),
                $request,
                $response
            )
        );

        return $response;
    }

    /**
     * @param string $routename
     * @param array  $parameters
     *
     * @return RedirectResponse
     */
    private function redirectToRoute($routename, array $parameters = []): RedirectResponse
    {
        $url = $this->router->generate($routename, $parameters);

        return new RedirectResponse($url);
    }
}
