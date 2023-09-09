<?php

namespace App\MessageHandler;

use App\Component\CbrSoapClient\CbrSoapClient;
use App\Message\CurrencyRateTask;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class CurrencyRateTaskHandler
{
    public function __construct(
        private readonly CbrSoapClient   $cbrSoapClient,
        private readonly LoggerInterface $logger
    )
    {
    }

    /**
     * @param CurrencyRateTask $task
     *
     * @throws InvalidArgumentException
     * @throws \Throwable
     */
    public function __invoke(CurrencyRateTask $task)
    {
        try {
            $this->cbrSoapClient->getCursOnDate($task->getDate());
        }
        catch (\Throwable $e) {
            $this->logger->error("{$e->getMessage()} ({$e->getFile()}: {$e->getLine()})\n{$e->getTraceAsString()}");

            throw $e;
        }
    }
}