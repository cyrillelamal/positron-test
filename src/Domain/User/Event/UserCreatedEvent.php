<?php

namespace App\Domain\User\Event;

use App\Entity\User;

class UserCreatedEvent
{
    public const NAME = 'user.created';

    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
