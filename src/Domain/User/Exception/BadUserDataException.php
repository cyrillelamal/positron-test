<?php

namespace App\Domain\User\Exception;

use Exception;
use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class BadUserDataException extends Exception
{
    private ConstraintViolationListInterface $errors;

    public function __construct(
        ConstraintViolationListInterface                                             $errors,
        #[LanguageLevelTypeAware(['8.0' => 'string'], default: '')]                  $message = "",
        #[LanguageLevelTypeAware(['8.0' => 'int'], default: '')]                     $code = 0,
        #[LanguageLevelTypeAware(['8.0' => 'Throwable|null'], default: 'Throwable')] $previous = null
    )
    {
        parent::__construct($message, $code, $previous);

        $this->errors = $errors;
    }

    public function getErrors(): ConstraintViolationListInterface
    {
        return $this->errors;
    }
}