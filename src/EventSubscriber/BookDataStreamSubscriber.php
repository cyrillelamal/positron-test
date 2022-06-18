<?php

namespace App\EventSubscriber;

use App\Service\BookData\Event\BookDataStreamedEvent;
use Exception;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BookDataStreamSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            BookDataStreamedEvent::NAME => 'mutateProperties',
        ];
    }

    /**
     * @throws Exception
     */
    public function mutateProperties(BookDataStreamedEvent $event): void
    {
        $data = $event->getData();

        $this->replacePublishedDate($data);
    }

    /**
     * @throws Exception
     */
    protected function replacePublishedDate(object $data): void
    {
        /*
        {#3127
          +"title": "Unlocking Android"
          +"isbn": "1933988673"
          +"publishedDate": {#6061
            +"$date": "2009-04-01T00:00:00.000-0700"
          }
        ...
         */
        if (property_exists($data, 'publishedDate')
            && is_object($data->publishedDate)
            && property_exists($data->publishedDate, '$date')
        ) {
            $data->publishedDate = $data->publishedDate->{'$date'}; // string
        }
    }
}
