<?php

namespace App\Validator;

use DateTime;
use Exception;

class DatesValidator
{
    /**
     * @param string $date
     * @param string $maxDate
     *
     * @return DateTime
     * @throws Exception
     */
    public static function validate(string $date, string $maxDate): DateTime
    {
        try {
            $date = new DateTime($date);
        }
        catch (Exception $e) {
            throw new Exception('Incorrect $date value "' . $date . '"');
        }

        try {
            $maxDate = new DateTime($maxDate);
        }
        catch (Exception $e) {
            throw new Exception('Incorrect $maxDate value "' . $maxDate . '"');
        }

        $date->setTime(0, 0);
        $maxDate->setTime(0, 0);

        if ($date > $maxDate) {
            throw new Exception('$date cannot be greater than ' . $maxDate->format('Y-m-d'));
        }

        return $date;
    }
}