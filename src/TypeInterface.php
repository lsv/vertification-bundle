<?php

namespace Lsv\Vertification;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface TypeInterface
{
    /**
     * The unique key for the type
     * Should only be [a-z] string, as it will be used as the key in the route.
     *
     * @return string
     */
    public function getKey(): string;

    /**
     * The title of the type
     * Should be the translated title of the type.
     *
     * @param Request $request
     *
     * @return string
     */
    public function getTitle(Request $request): string;

    /**
     * Is this type enabled with the options send.
     *
     * @param array|null $options The options which are sent to the TypeHandler methods
     *
     * @return bool
     */
    public function isEnabled(array $options = null): bool;

    /**
     * Is the User already validated.
     *
     * @param ValidationUserInterface $user
     *
     * @return bool
     */
    public function isValidated(ValidationUserInterface $user): bool;

    /**
     * Has the user already trying to get validated, and are just waiting for response.
     *
     * @param ValidationUserInterface $user
     *
     * @return bool
     */
    public function awaitingValidated(ValidationUserInterface $user): bool;

    /**
     * Build the form for the request.
     *
     * @param ValidationUserInterface $user
     * @param Request                 $request
     * @param array                   $formOptions
     *
     * @return FormInterface
     */
    public function requestForm(ValidationUserInterface $user, Request $request, array $formOptions = []): FormInterface;

    /**
     * Create the request template.
     *
     * @param ValidationUserInterface $user
     * @param FormInterface           $form
     * @param TypeInterface           $type
     *
     * @return Response
     */
    public function requestTemplate(ValidationUserInterface $user, FormInterface $form, self $type): Response;

    /**
     * Handle the form request.
     *
     * @param ValidationUserInterface $user
     * @param FormInterface           $form
     * @param Request                 $request
     */
    public function handleRequestForm(ValidationUserInterface $user, FormInterface $form, Request $request): void;
}
