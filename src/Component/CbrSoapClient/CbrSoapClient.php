<?php

namespace App\Component\CbrSoapClient;

use DateTime;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use SoapClient;
use SoapFault;
use SoapVar;
use Symfony\Component\Cache\Adapter\MemcachedAdapter;
use Symfony\Component\Cache\Exception\CacheException;
use Symfony\Contracts\Cache\ItemInterface;

class CbrSoapClient
{
    private string           $cbrUri  = 'http://web.cbr.ru/';
    private string           $soapUrl = 'http://www.cbr.ru/DailyInfoWebServ/DailyInfo.asmx';
    private SoapClient       $client;
    private MemcachedAdapter $cache;

    /**
     * @throws SoapFault
     * @throws \ErrorException
     * @throws CacheException
     */
    public function __construct(
        private readonly LoggerInterface $logger
    )
    {
        $this->client = new SoapClient(null, [
            'location'     => $this->soapUrl,
            'uri'          => $this->cbrUri,
            'soap_version' => SOAP_1_2,
            'style'        => SOAP_DOCUMENT,
            'use'          => SOAP_LITERAL,
            'trace'        => true,
            'encoding'     => 'UTF-8',
        ]);

        $memcachedClient = MemcachedAdapter::createConnection(
            'memcached://cbr_memcached'
        );
        $this->cache     = new MemcachedAdapter($memcachedClient, 'cbr');
    }

    /**
     * Получение курсов валют на определенную дату (ежедневные курсы валют)
     *
     * @param DateTime $date
     *
     * @return array|CurrencyRateEntity[]
     * @throws InvalidArgumentException
     */
    public function getCursOnDate(DateTime $date): array
    {
        $date = $date->format('Y-m-d');

        $this->logger->info("Start getCursOnDate {$date}");

        return $this->cache->get(
            'getCursOnDate_date_' . $date,
            function (ItemInterface $item) use ($date): array {
                $soapRequest = new SoapVar(
                    '<GetCursOnDateXML xmlns="' . $this->cbrUri . '">
                        <On_date>' . $date . '</On_date>
                     </GetCursOnDateXML>',
                    XSD_ANYXML
                );

                $response = $this->client->__soapCall('GetCursOnDateXML', [$soapRequest]);

                $this->logger->info("getCursOnDate {$date} response", (array)$response);

                return $response->ValuteData->ValuteCursOnDate;
            }
        );
    }

    /**
     * Последняя дата публикации курсов валют как DateTime (ежедневные валюты)
     *
     * @return string
     * @throws InvalidArgumentException
     */
    public function getLatestDateTime(): string
    {
        return $this->cache->get(
            'getLatestDateTime',
            function (ItemInterface $item): string {
                $item->expiresAfter(60);

                $soapRequest = new SoapVar(
                    '<GetLatestDateTime xmlns="' . $this->cbrUri . '"></GetLatestDateTime>',
                    XSD_ANYXML
                );

                return $this->client->__soapCall('GetCursOnDateXML', [$soapRequest]);
            }
        );
    }
}
