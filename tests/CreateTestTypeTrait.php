<?php

namespace Lsv\VertificationTest;

use Lsv\Vertification\TypeInterface;
use Lsv\Vertification\TypeResponseInterface;
use Lsv\Vertification\ValidationUserInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

trait CreateTestTypeTrait
{

    protected function createResponseType(
        $name,
        $enabled = true,
        $validated = false,
        $awaitingValidation = false,
        $requestForm = null,
        $requestTemplate = null,
        $handleRequestForm = null,
        $responseFormKey = null,
        $handleResponseFormKey = null,
        $responseTemplateKey = null
    )
    {
        $class = new class implements TypeInterface, TypeResponseInterface
        {
            public $key, $enabled, $validated, $awaiting, $requestFormKey, $requestTemplateKey, $handleRequestFormKey, $responseFormKey, $handleResponseFormKey, $responseTemplateKey;
            public function getKey(): string
            {
                return $this->key;
            }

            public function getTitle(Request $request): string
            {
                return $this->key;
            }

            public function isEnabled(array $options = null): bool
            {
                return $this->enabled;
            }

            public function isValidated(ValidationUserInterface $user): bool
            {
                return $this->validated;
            }

            public function awaitingValidated(ValidationUserInterface $user): bool
            {
                return $this->awaiting;
            }

            public function requestForm(ValidationUserInterface $user, Request $request, array $formOptions = []): FormInterface
            {
                return $this->requestFormKey;
            }

            public function requestTemplate(ValidationUserInterface $user, FormInterface $form, TypeInterface $type): Response
            {
                return $this->requestTemplateKey;
            }

            public function handleRequestForm(ValidationUserInterface $user, FormInterface $form, Request $request): void
            {
                $this->handleRequestFormKey;
            }

            public function responseForm(ValidationUserInterface $user, Request $request, array $formOptions = []): FormInterface
            {
                return $this->responseFormKey;
            }

            public function handleResponseForm(ValidationUserInterface $user, FormInterface $form, Request $request): void
            {
                $this->handleResponseFormKey;
            }

            public function responseTemplate(ValidationUserInterface $user, FormInterface $form, TypeInterface $type): Response
            {
                return $this->responseTemplateKey;
            }
        };

        $class->key = $name;
        $class->enabled = $enabled;
        $class->validated = $validated;
        $class->awaiting = $awaitingValidation;
        $class->requestFormKey = $requestForm;
        $class->requestTemplateKey = $requestTemplate;
        $class->handleRequestFormKey = $handleRequestForm;
        $class->responseFormKey = $responseFormKey;
        $class->responseTemplateKey = $responseTemplateKey;
        $class->handleResponseFormKey = $handleResponseFormKey;
        $class->responseTemplateKey = $responseTemplateKey;

        return $class;
    }

    /**
     * @param string $name
     * @param bool|callable $enabled
     * @param bool|callable $validated
     * @param bool|callable $awaitingValidation
     * @param null|FormInterface $requestForm
     * @param null|Response $requestTemplate
     * @param null|callable $handleRequestForm
     *
     * @return TypeInterface
     */
    protected function createType(
        $name,
        $enabled = true,
        $validated = false,
        $awaitingValidation = false,
        $requestForm = null,
        $requestTemplate = null,
        $handleRequestForm = null
    ) : TypeInterface {
        $class = new class implements TypeInterface
        {
            public $key, $enabled, $validated, $awaiting, $requestFormKey, $requestTemplateKey, $handleRequestFormKey;

            public function getKey(): string
            {
                return $this->key;
            }

            public function getTitle(Request $request): string
            {
                return $this->key;
            }

            public function isEnabled(array $options = null): bool
            {
                if (\is_callable($this->enabled)) {
                    return \call_user_func($this->enabled, $options);
                }

                return $this->enabled;
            }

            public function isValidated(ValidationUserInterface $user): bool
            {
                if (\is_callable($this->validated)) {
                    return \call_user_func($this->validated, $user);
                }
                return $this->validated;
            }

            public function awaitingValidated(ValidationUserInterface $user): bool
            {
                if (\is_callable($this->awaiting)) {
                    return \call_user_func($this->awaiting, $user);
                }
                return $this->awaiting;
            }

            public function requestForm(ValidationUserInterface $user, Request $request, array $formOptions = []): FormInterface
            {
                return $this->requestFormKey;
            }

            public function requestTemplate(ValidationUserInterface $user, FormInterface $form, TypeInterface $type): Response
            {
                return $this->requestTemplateKey;
            }

            public function handleRequestForm(ValidationUserInterface $user, FormInterface $form, Request $request): void
            {
                if (\is_callable($this->handleRequestFormKey)) {
                    $this->handleRequestFormKey;
                }
            }
        };

        $class->key = $name;
        $class->enabled = $enabled;
        $class->validated = $validated;
        $class->awaiting = $awaitingValidation;
        $class->requestFormKey = $requestForm;
        $class->requestTemplateKey = $requestTemplate;
        $class->handleRequestFormKey = $handleRequestForm;

        return $class;
    }

}
