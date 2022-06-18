<?php

namespace App\Service\BookData\Event;

use StdClass;

class BookDataStreamedEvent
{
    public const NAME = 'book_data.streamed';

    private StdClass $data;

    public function __construct(StdClass $data)
    {
        $this->data = $data;
    }

    public function getData(): StdClass
    {
        return $this->data;
    }
}
