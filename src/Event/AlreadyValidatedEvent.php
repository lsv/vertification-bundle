<?php

namespace Lsv\Vertification\Event;

use Lsv\Vertification\TypeInterface;
use Lsv\Vertification\ValidationUserInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AlreadyValidatedEvent extends Event
{
    public const NAME = 'lsv_vertification_user_alreadyvalidated';

    /**
     * @var TypeInterface
     */
    private $type;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Response
     */
    private $response;

    /**
     * @var ValidationUserInterface
     */
    private $user;

    public function __construct(ValidationUserInterface $user, TypeInterface $type, Request $request, Response $response)
    {
        $this->type = $type;
        $this->request = $request;
        $this->response = $response;
        $this->user = $user;
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
     * Get Type.
     *
     * @return TypeInterface
     */
    public function getType(): TypeInterface
    {
        return $this->type;
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
