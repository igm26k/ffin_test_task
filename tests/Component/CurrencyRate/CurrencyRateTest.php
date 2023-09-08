<?php

namespace App\Tests\Component\CurrencyRate;

use App\Component\CbrSoapClient\CbrSoapClient;
use App\Component\CurrencyRate\CurrencyRate;
use DateTime;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class CurrencyRateTest extends KernelTestCase
{
    public static function getByProvider(): array
    {
        return [
            ['2023-09-05', 'JPY', 'USD', 178.1476, 182.8144, '-2.5528%'],
            ['2023-09-05', 'USD', 'RUB', 0.01027, 0.01039, '-1.0889%'],
            ['2023-09-05', 'RUB', 'USD', 97.35, 96.29, '+1.1008%'],
            ['2023-09-05', 'RUB', 'RUB', 1.0, 1.0, '0%'],
        ];
    }

    /**
     * @dataProvider getByProvider
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public function testGetBy(
        $date,
        $currencyCode,
        $baseCurrencyCode,
        $expectedDateRate,
        $expectedPrevDateRate,
        $expectedDiff
    )
    {
        $dateObj     = new DateTime($date);
        $prevDateObj = (new DateTime($date))->modify('-1 day');

        $cbrSoapClientMock = $this->getMockBuilder(CbrSoapClient::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cbrSoapClientMock
            ->method('getCursOnDate')
            ->willReturnCallback(function ($value) use ($dateObj, $prevDateObj) {
                if ($value == $dateObj) {
                    return [
                        (object)[
                            'Vname'     => 'Американский доллар',
                            'Vnom'      => 1,
                            'Vcurs'     => 97.35,
                            'Vcode'     => 101,
                            'VchCode'   => 'USD',
                            'VunitRate' => 97.35 / 1,
                        ],
                        (object)[
                            'Vname'     => 'Японская иена',
                            'Vnom'      => 100,
                            'Vcurs'     => 54.6457,
                            'Vcode'     => 392,
                            'VchCode'   => 'JPY',
                            'VunitRate' => 54.6457 / 100,
                        ],
                    ];
                }

                if ($value == $prevDateObj) {
                    return [
                        (object)[
                            'Vname'     => 'Американский доллар',
                            'Vnom'      => 1,
                            'Vcurs'     => 96.29,
                            'Vcode'     => 101,
                            'VchCode'   => 'USD',
                            'VunitRate' => 96.29 / 1,
                        ],
                        (object)[
                            'Vname'     => 'Японская иена',
                            'Vnom'      => 100,
                            'Vcurs'     => 52.6709,
                            'Vcode'     => 392,
                            'VchCode'   => 'JPY',
                            'VunitRate' => 52.6709 / 100,
                        ],
                    ];
                }

                return false;
            });

        $cbrSoapClientMock
            ->method('getLatestDateTime')
            ->willReturnCallback(function () {
                return '2023-09-05T00:00:00';
            });

        $currencyRate = new CurrencyRate($cbrSoapClientMock);
        $result       = $currencyRate->getBy($date, $currencyCode, $baseCurrencyCode);

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);

        $this->assertArrayHasKey('pair', $result);
        $this->assertArrayHasKey('dateRate', $result);
        $this->assertArrayHasKey('prevDateRate', $result);
        $this->assertArrayHasKey('diff', $result);

        $this->assertEquals("{$baseCurrencyCode}/{$currencyCode}", $result['pair']);
        $this->assertEquals($expectedDateRate, $result['dateRate']);
        $this->assertEquals($expectedPrevDateRate, $result['prevDateRate']);
        $this->assertEquals($expectedDiff, $result['diff']);
    }
}
