<?php

namespace App\Component\CurrencyRate;

use App\Component\CbrSoapClient\CbrSoapClient;
use App\Validator\DatesValidator;
use DateTime;
use Exception;
use Psr\Cache\InvalidArgumentException;

class CurrencyRate
{
    public function __construct(
        private readonly CbrSoapClient $cbrSoapClient
    )
    {
    }

    /**
     * @param string $date
     * @param string $currencyCode
     * @param string $baseCurrencyCode
     *
     * @return array
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function getBy(string $date, string $currencyCode, string $baseCurrencyCode = 'RUB'): array
    {
        $lastDateTime = $this->cbrSoapClient->getLatestDateTime(); // получение максимально возможной даты с cbr.ru
        $date         = DatesValidator::validate($date, $lastDateTime);

        // Если выбранная дата выпадает на воскресенье или понедельник,
        // то смещаем ее на дату последнего обновления курса, т.е. на субботу
        if (in_array($date->format('N'), [7, 1])) {
            $date->modify('last saturday');
        }

        // Затем, просто отнимаем один день, чтобы получить предыдущий торговый день
        $prevDate = clone $date;
        $prevDate->modify('-1 day');

        // Вычисляем курсы за указанный и предыдущий торговые дни
        $dateRate     = $this->getCurrencyRate($date, $currencyCode, $baseCurrencyCode);
        $prevDateRate = $this->getCurrencyRate($prevDate, $currencyCode, $baseCurrencyCode);

        // Вычисляем изменение в процентах
        $diff = round((($dateRate - $prevDateRate) / $prevDateRate * 100), 4);

        return [
            'pair'         => "{$baseCurrencyCode}/{$currencyCode}",
            'dateRate'     => $this->round($dateRate),
            'prevDateRate' => $this->round($prevDateRate),
            'diff'         => $diff > 0 ? "+{$diff}%" : "{$diff}%",
        ];
    }

    /**
     * Округление до 4 значимых цифр
     *
     * @param float|int $number
     *
     * @return float
     */
    private function round(float|int $number): float
    {
        $roundedNumber = round($number, 4, PHP_ROUND_HALF_DOWN);

        return ($roundedNumber < 0.1) ? round($number, 3 - floor(log10($number))) : $roundedNumber;
    }

    /**
     * @param DateTime $date
     * @param string   $currencyCode
     * @param string   $baseCurrencyCode
     *
     * @return float|int
     * @throws InvalidArgumentException
     * @throws Exception
     */
    private function getCurrencyRate(DateTime $date, string $currencyCode, string $baseCurrencyCode = 'RUB'): float|int
    {
        // Получение курсов на указанную дату
        $rates = $this->cbrSoapClient->getCursOnDate($date);

        // Находим среди курсов указанные коды валют
        foreach ($rates as $rate) {
            if ($rate->VchCode === $currencyCode && $currencyCode !== 'RUB') {
                $currency = $rate;
            }

            if ($rate->VchCode === $baseCurrencyCode && $baseCurrencyCode !== 'RUB') {
                $baseCurrency = $rate;
            }
        }

        // Обработка ситуаций, когда передан некорректный код валюты (не удалось найти)
        if ($currencyCode !== 'RUB' && empty($currency)) {
            throw new Exception('The specified currency code was not found');
        }
        if ($baseCurrencyCode !== 'RUB' && empty($baseCurrency)) {
            throw new Exception('The specified base currency code was not found');
        }

        // Если указаны одинаковые коды валют, то сразу возвращаем 1
        if ($currencyCode === $baseCurrencyCode) {
            return 1;
        }

        // Обрабатываем ситуации, когда одна из указанных валют RUB
        if ($baseCurrencyCode === 'RUB') {
            return $currency->Vnom / $currency->Vcurs;
        }
        if ($currencyCode === 'RUB') {
            return $baseCurrency->VunitRate;
        }

        // Иначе считаем кросс-курс через рубль
        return $baseCurrency->VunitRate / $currency->VunitRate;
    }
}