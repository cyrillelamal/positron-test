<?php

namespace App\EventSubscriber;

use App\Domain\Book\Event\BookCreatedEvent;
use App\Domain\Category\UseCase\AddDefaultCategory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BookCrudSubscriber implements EventSubscriberInterface
{
    private AddDefaultCategory $addDefaultCategory;

    public static function getSubscribedEvents(): array
    {
        return [
            BookCreatedEvent::NAME => 'handleBookCreated',
        ];
    }

    public function __construct(
        AddDefaultCategory $addDefaultCategory,
    )
    {
        $this->addDefaultCategory = $addDefaultCategory;
    }

    public function handleBookCreated(BookCreatedEvent $event): void
    {
        $book = $event->getBook();

        if ($book->hasNoCategories()) {
            ($this->addDefaultCategory)($book);
        }
    }
}
