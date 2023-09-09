<?php

namespace App\Tests\Validator;

use App\Validator\DatesValidator;
use PHPUnit\Framework\TestCase;

/**
 * Unit тесты валидатора дат
 */
class DateValidatorTest extends TestCase
{
    /**
     * Переданы корректные даты
     *
     * @return void
     * @throws \Exception
     */
    public function testValidateDate()
    {
        $dateObj    = new \DateTime();
        $maxDateObj = (new \DateTime())->modify('+1 day');

        $date    = $dateObj->format('d.m.Y');
        $maxDate = $maxDateObj->format('Y-m-d') . 'T00:00:00';

        $successResult = DatesValidator::validate($date, $maxDate);
        $this->assertInstanceOf(\DateTime::class, $successResult);
    }

    /**
     * $date > $maxDate
     *
     * @return void
     * @throws \Exception
     */
    public function testValidateDateGreaterThanLastDate()
    {
        $date    = (new \DateTime())->modify('+2 day')->format('Y-m-d');
        $maxDate = (new \DateTime())->modify('+1 day')->format('Y-m-d');

        $this->expectException(\Exception::class);
        DatesValidator::validate($date, $maxDate);
    }

    /**
     * Некорректный формат $date
     *
     * @return void
     * @throws \Exception
     */
    public function testValidateDateWrongDate()
    {
        $date    = 'omgwtf';
        $maxDate = (new \DateTime())->modify('+1 day')->format('Y-m-d');

        $this->expectException(\Exception::class);
        DatesValidator::validate($date, $maxDate);
    }

    /**
     * Некорректный формат $maxDate
     *
     * @return void
     * @throws \Exception
     */
    public function testValidateDateWrongLastDate()
    {
        $date    = (new \DateTime())->modify('+1 day')->format('Y-m-d');
        $maxDate = 'omgwtf';

        $this->expectException(\Exception::class);
        DatesValidator::validate($date, $maxDate);
    }
}
