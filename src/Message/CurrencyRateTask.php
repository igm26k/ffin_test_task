<?php

namespace App\Message;

use DateTime;

class CurrencyRateTask
{
    public function __construct(private readonly DateTime $date)
    {
    }

    public function getDate(): DateTime
    {
        return $this->date;
    }
}