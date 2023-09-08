<?php

namespace App\Command;

use App\Component\CurrencyRate\CurrencyRate;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

#[AsCommand(
    name       : 'app:get-currency-rate',
    description: 'Получение курсов, кроскурсов ЦБ заданной пары валют за указанную дату',
)]
class GetCurrencyRateCommand extends Command
{
    public function __construct(
        private readonly CurrencyRate    $currencyRate,
        private readonly LoggerInterface $logger
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('date', 'd', InputOption::VALUE_REQUIRED,
                'Дата в любом валидном формате')
            ->addOption('currencyCode', 'c', InputOption::VALUE_REQUIRED,
                'Код валюты')
            ->addOption('baseCurrencyCode', 'b', InputOption::VALUE_OPTIONAL,
                'Код базовой валюты', 'RUB');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $date = $input->getOption('date');

        try {
            $result = $this->currencyRate->getBy(
                $date,
                $input->getOption('currencyCode'),
                $input->getOption('baseCurrencyCode'),
            );

            $io->success("The {$result['pair']} currency pair exchange rate for {$date} is {$result['dateRate']}. " .
                "The difference with the previous trading day {$result['diff']}.");
        }
        catch (Throwable $e) {
            $io->error($e->getMessage());
            $this->logger->error("{$e->getMessage()} ({$e->getFile()}: {$e->getLine()})\n{$e->getTraceAsString()}");
        }

        return Command::SUCCESS;
    }
}
