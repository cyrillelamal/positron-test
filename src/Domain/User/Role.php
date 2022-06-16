<?php

namespace App\Domain\User;

/**
 * @todo PHP8.1 enum
 */
class Role
{
    public const PREFIX = 'ROLE_';

    public const USER = self::PREFIX . 'USER';
    public const ADMIN = self::PREFIX . 'ADMIN';
}
