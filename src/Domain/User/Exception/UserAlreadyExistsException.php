<?php

namespace App\Domain\User\Exception;

use Exception;
use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;

class UserAlreadyExistsException extends Exception
{
    private string $userIdentifier = '';

    public function __construct(
        string                                                                       $userIdentifier,
        #[LanguageLevelTypeAware(['8.0' => 'string'], default: '')]                  $message = "",
        #[LanguageLevelTypeAware(['8.0' => 'int'], default: '')]                     $code = 0,
        #[LanguageLevelTypeAware(['8.0' => 'Throwable|null'], default: 'Throwable')] $previous = null
    )
    {
        parent::__construct($message, $code, $previous);

        $this->userIdentifier = $userIdentifier;
    }

    /**
     * Already existing identifier.
     */
    public function getUserIdentifier(): string
    {
        return $this->userIdentifier;
    }
}
