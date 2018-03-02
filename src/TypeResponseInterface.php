<?php

namespace Lsv\Vertification;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Add this interface to your type if you need get a response from the user (eg recieved email code or SMS code).
 *
 * Fx. Create a response form, where the user can enter his recieved SMS code
 */
interface TypeResponseInterface
{
    /**
     * Build the form for the response.
     *
     * @param ValidationUserInterface $user
     * @param Request                 $request
     * @param array                   $formOptions
     *
     * @return FormInterface
     */
    public function responseForm(ValidationUserInterface $user, Request $request, array $formOptions = []): FormInterface;

    /**
     * Handle the form response.
     *
     * @param ValidationUserInterface $user
     * @param FormInterface           $form
     * @param Request                 $request
     */
    public function handleResponseForm(ValidationUserInterface $user, FormInterface $form, Request $request): void;

    /**
     * Create the response template.
     *
     * @param ValidationUserInterface $user
     * @param FormInterface           $form
     * @param TypeInterface           $type
     *
     * @return Response
     */
    public function responseTemplate(ValidationUserInterface $user, FormInterface $form, TypeInterface $type): Response;
}
