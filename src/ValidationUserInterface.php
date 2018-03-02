<?php

namespace Lsv\Vertification;

interface ValidationUserInterface
{
    /**
     * Return if the user needs validation.
     *
     * @return bool
     */
    public function isValidationRequired(): bool;

    /**
     * Return the validation type key.
     *
     * @return null|string
     */
    public function getValidationTypeKey(): ?string;
}
