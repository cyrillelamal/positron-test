<?php

namespace App\Domain\Feedback\Event;

use App\Entity\Feedback;

class FeedbackCreatedEvent
{
    public const NAME = 'feedback.created';

    private Feedback $feedback;

    public function __construct(Feedback $feedback)
    {
        $this->feedback = $feedback;
    }

    public function getFeedback(): Feedback
    {
        return $this->feedback;
    }
}
