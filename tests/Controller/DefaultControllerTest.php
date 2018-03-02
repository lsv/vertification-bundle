<?php

namespace Lsv\VertificationTest\Controller;

use Lsv\Vertification\Controller\DefaultController;
use Lsv\Vertification\Handler\TypeHandler;
use Lsv\Vertification\ValidationUserInterface;
use Lsv\VertificationTest\CreateTestHandlerTrait;
use Lsv\VertificationTest\CreateTestTypeTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class DefaultControllerTest extends TestCase
{

    use CreateTestHandlerTrait;
    use CreateTestTypeTrait;

    /**
     * @param TypeHandler $handler
     * @param TokenStorageInterface|null $storage
     *
     * @return DefaultController
     */
    protected function getController(TypeHandler $handler, TokenStorageInterface $storage = null): DefaultController
    {
        /** @var EventDispatcherInterface $eventDispatcherMock */
        $eventDispatcherMock = $this->createMock(EventDispatcherInterface::class);

        $routerMock = $this->getMockBuilder(Router::class)
            ->disableOriginalConstructor()
            ->setMethods(['generate'])
            ->getMock()
        ;

        $routerMock
            ->expects($this->any())
            ->method('generate')
            ->with($this->logicalOr(
                $this->equalTo('lsv_vertification_request'),
                $this->equalTo('lsv_vertification_response'),
                $this->equalTo('')
            ))
            ->will($this->returnCallback(function($route) {
                if ($route === 'lsv_vertification_request') {
                    return '/key1';
                }

                if ($route === 'lsv_vertification_response') {
                    return '/key1/response';
                }

                return '/';
            }))
        ;

        if (! $storage) {
            $tokenStorageMock = $this->getMockBuilder(TokenStorageInterface::class)
                ->setMethods(['getToken', 'setToken'])
                ->getMock()
            ;

            $tokenStorageMock
                ->expects($this->never())
                ->method('setToken');

            $tokenStorageMock
                ->expects($this->any())
                ->method('getToken')
                ->willReturn(new class
                {
                    public function getUser(): ValidationUserInterface
                    {
                        return new class implements ValidationUserInterface
                        {
                            public function isValidationRequired(): bool
                            {
                                return true;
                            }

                            public function getValidationTypeKey(): string
                            {
                                return 'key1';
                            }
                        };
                    }
                })
            ;
            /** @var TokenStorageInterface $tokenStorageMock */
            $storage = $tokenStorageMock;
        }

        /** @var RouterInterface $routerMock */

        return new DefaultController(
            '',
            '',
            '',
            [],
            $eventDispatcherMock,
            $handler,
            $routerMock,
            $storage
        );
    }

    public function testRequest(): void
    {
        $formMock = $this->createMock(FormInterface::class);
        $response = new Response('form');
        $typeInterface = $this->createType('key1', true, false, false, $formMock, $response);

        $controller = $this->getController($this->getHandler([$typeInterface]));

        $request = new Request();
        $type = 'key1';
        $response = $controller->request($request, $type);
        $this->assertEquals('form', $response->getContent());
    }

    public function testRequestUserIsValidated(): void
    {
        $formMock = $this->createMock(FormInterface::class);
        $response = new Response('form');
        $typeInterface = $this->createType('key1', true, true, false, $formMock, $response);

        $controller = $this->getController($this->getHandler([$typeInterface]));

        $request = new Request();
        $type = 'key1';
        $response = $controller->request($request, $type);
        $this->assertContains('<title>Redirecting to /</title>', $response->getContent());
    }

    public function testRequestUserIsAwaitingValidation(): void
    {
        $formMock = $this->createMock(FormInterface::class)
            ->expects($this->any())
            ->method('isSubmitted')
            ->willReturn(true)
        ;

        $response = new Response('form');
        $typeInterface = $this->createType('key1', true, false, true, $formMock, $response);

        $controller = $this->getController($this->getHandler([$typeInterface]));

        $request = new Request();
        $type = 'key1';
        $response = $controller->request($request, $type);
        $this->assertContains('<title>Redirecting to /</title>', $response->getContent());
    }

    public function testRequestSubmittedForm(): void
    {
        $formMock = $this->createMock(FormInterface::class);
        $formMock
            ->expects($this->once())
            ->method('isSubmitted')
            ->willReturn(true)
        ;
        $formMock
            ->expects($this->once())
            ->method('isValid')
            ->willReturn(true)
        ;

        $response = new Response('form');
        $typeInterface = $this->createType('key1', true, false, false, $formMock, $response);

        $controller = $this->getController(
            $this->getHandler([$typeInterface])
        );

        $request = new Request();
        $type = 'key1';
        $response = $controller->request($request, $type);
        $this->assertContains('<title>Redirecting to /</title>', $response->getContent());
    }

    public function testRequestSubmittedFormWithResponse(): void
    {
        $requestForm = $this->createMock(FormInterface::class);
        $requestForm
            ->expects($this->once())
            ->method('isSubmitted')
            ->willReturn(true)
        ;
        $requestForm
            ->expects($this->once())
            ->method('isValid')
            ->willReturn(true)
        ;
        $requestResponse = new Response('form');

        $responseForm = $this->createMock(FormInterface::class);
        $responseForm
            ->expects($this->any())
            ->method('isSubmitted')
            ->willReturn(true)
        ;
        $responseForm
            ->expects($this->any())
            ->method('isValid')
            ->willReturn(true)
        ;
        $responseResponse = new Response('form');

        $typeInterface = $this->createResponseType(
            'key1',
            true,
            false,
            false,
            $requestForm,
            $requestResponse,
            null,
            $responseForm,
            $responseResponse
        );

        $controller = $this->getController(
            $this->getHandler([$typeInterface])
        );

        $request = new Request();
        $type = 'key1';
        $response = $controller->request($request, $type);
        $this->assertContains('<title>Redirecting to /key1/response</title>', $response->getContent());
    }

    public function testResponseUserIsValidated(): void
    {
        $typeInterface = $this->createType('key1', true, true, false);

        $controller = $this->getController($this->getHandler([$typeInterface]));

        $request = new Request();
        $type = 'key1';
        $response = $controller->response($request, $type);
        $this->assertContains('<title>Redirecting to /</title>', $response->getContent());
    }

    public function testResponseUserIsAwaitingValidation(): void
    {
        $typeInterface = $this->createType('key1', true, false, true);

        $controller = $this->getController($this->getHandler([$typeInterface]));

        $request = new Request();
        $type = 'key1';
        $response = $controller->response($request, $type);
        $this->assertContains('<title>Redirecting to /</title>', $response->getContent());
    }

    public function testResponseTypekeyNotAvailable(): void
    {
        $typeInterface = $this->createType('key1', true, false, true);

        $controller = $this->getController($this->getHandler([$typeInterface]));

        $request = new Request();
        $type = 'key2';
        $response = $controller->response($request, $type);
        $this->assertContains('<title>Redirecting to /key1</title>', $response->getContent());
    }

    public function testResponseTypekeyNotResponse(): void
    {
        $typeInterface = $this->createType('key1', true, false, false);

        $controller = $this->getController($this->getHandler([$typeInterface]));

        $request = new Request();
        $type = 'key1';
        $response = $controller->response($request, $type);
        $this->assertContains('<title>Redirecting to /key1</title>', $response->getContent());
    }

    public function testResponseTypekeySubmittedForm(): void
    {
        $responseForm = $this->createMock(FormInterface::class);
        $responseForm
            ->expects($this->any())
            ->method('isSubmitted')
            ->willReturn(true)
        ;
        $responseForm
            ->expects($this->any())
            ->method('isValid')
            ->willReturn(true)
        ;

        $typeInterface = $this->createResponseType('key1', true, false, false, null, null, null, $responseForm);

        $controller = $this->getController($this->getHandler([$typeInterface]));

        $request = new Request();
        $type = 'key1';
        $response = $controller->response($request, $type);
        $this->assertContains('<title>Redirecting to /</title>', $response->getContent());
    }

    public function testResponseTypekeyNotSubmittedForm(): void
    {
        $responseForm = $this->createMock(FormInterface::class);
        $responseForm
            ->expects($this->any())
            ->method('isSubmitted')
            ->willReturn(false)
        ;

        $response = new Response('form');
        $typeInterface = $this->createResponseType('key1', true, false, false, null, null, null, $responseForm, null, $response);

        $controller = $this->getController($this->getHandler([$typeInterface]));

        $request = new Request();
        $type = 'key1';
        $response = $controller->response($request, $type);
        $this->assertEquals('form', $response->getContent());
    }

    public function testUserTokenNotSet(): void
    {
        $tokenStorageMock = $this->getMockBuilder(TokenStorageInterface::class)
            ->setMethods(['getToken', 'setToken'])
            ->getMock()
        ;

        $tokenStorageMock
            ->expects($this->never())
            ->method('setToken');

        $tokenStorageMock
            ->expects($this->any())
            ->method('getToken')
            ->willReturn(null)
        ;

        $typeInterface = $this->createResponseType('key1');
        $controller = $this->getController($this->getHandler([$typeInterface]), $tokenStorageMock);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('User token needs to be set');

        $controller->request(new Request(), $typeInterface->getKey());
    }

    public function testUserTokenNotSetOnUser(): void
    {
        $tokenStorageMock = $this->getMockBuilder(TokenStorageInterface::class)
            ->setMethods(['getToken', 'setToken'])
            ->getMock()
        ;

        $tokenStorageMock
            ->expects($this->never())
            ->method('setToken');

        $tokenStorageMock
            ->expects($this->any())
            ->method('getToken')
            ->willReturn(new class { public function getUser(): bool { return false; }})
        ;

        $typeInterface = $this->createResponseType('key1');
        $controller = $this->getController($this->getHandler([$typeInterface]), $tokenStorageMock);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('User token needs to be set');

        $controller->request(new Request(), $typeInterface->getKey());
    }

    public function testUserNotAValidationUser(): void
    {
        $tokenStorageMock = $this->getMockBuilder(TokenStorageInterface::class)
            ->setMethods(['getToken', 'setToken'])
            ->getMock()
        ;

        $tokenStorageMock
            ->expects($this->never())
            ->method('setToken');

        $tokenStorageMock
            ->expects($this->any())
            ->method('getToken')
            ->willReturn(new class {
                public function getUser(): object {
                    return new class {};
                }
            })
        ;

        $typeInterface = $this->createResponseType('key1');
        $controller = $this->getController($this->getHandler([$typeInterface]), $tokenStorageMock);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('User does not supports validation');

        $controller->request(new Request(), $typeInterface->getKey());
    }

}
