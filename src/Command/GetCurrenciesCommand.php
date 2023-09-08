<?php

namespace App\Command;

use App\Message\CurrencyRateTask;
use DateTime;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name       : 'app:get-currencies',
    description: 'Запустить сбор данных с cbr за 180 предыдущих дней',
)]
class GetCurrenciesCommand extends Command
{
    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly KernelInterface     $kernel
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $date = new DateTime();
        $days = 180;

        for ($day = 1; $day <= $days; $day++) {
            $dateObj = clone $date;
            $dateObj->modify("-{$day} days");

            $task = new CurrencyRateTask($dateObj);
            $this->bus->dispatch($task);
        }

        $env  = $this->kernel->getEnvironment();
        $date = date('Y-m-d');
        $io->success("Created {$days} CurrencyRateTask. You can see the execution log in ./var/log/{$env}-{$date}.log");

        return Command::SUCCESS;
    }
}
