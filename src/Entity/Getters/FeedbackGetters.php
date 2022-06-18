<?php

namespace App\Entity\Getters;

trait FeedbackGetters
{
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }
}