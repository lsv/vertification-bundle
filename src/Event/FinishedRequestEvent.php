<?php

namespace Lsv\Vertification\Event;

use Lsv\Vertification\TypeInterface;
use Lsv\Vertification\ValidationUserInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FinishedRequestEvent extends Event
{
    public const NAME = 'lsv_vertification_finished_request';

    /**
     * @var TypeInterface
     */
    private $type;

    /**
     * @var FormInterface
     */
    private $form;

    /**
     * @var ValidationUserInterface
     */
    private $user;

    /**
     * @var Response
     */
    private $response;

    /**
     * @var Request
     */
    private $request;

    public function __construct(TypeInterface $type, FormInterface $form, ValidationUserInterface $user, Request $request, Response $response)
    {
        $this->type = $type;
        $this->form = $form;
        $this->user = $user;
        $this->response = $response;
        $this->request = $request;
    }

    /**
     * Get Type.
     *
     * @return TypeInterface
     */
    public function getType(): TypeInterface
    {
        return $this->type;
    }

    /**
     * Get Form.
     *
     * @return FormInterface
     */
    public function getForm(): FormInterface
    {
        return $this->form;
    }

    /**
     * Get User.
     *
     * @return ValidationUserInterface
     */
    public function getUser(): ValidationUserInterface
    {
        return $this->user;
    }

    /**
     * Get Request.
     *
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * Get Response.
     *
     * @return Response
     */
    public function getResponse(): Response
    {
        return $this->response;
    }
}
