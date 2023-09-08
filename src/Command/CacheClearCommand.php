<?php

namespace App\Command;

use Symfony\Component\Cache\Adapter\MemcachedAdapter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name       : 'app:cache-clear',
    description: 'Очистить кеш memcached',
)]
class CacheClearCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $memcachedClient = MemcachedAdapter::createConnection(
            'memcached://cbr_memcached'
        );
        $this->cache     = new MemcachedAdapter($memcachedClient, 'cbr');
        $this->cache->clear('cbr');

        $io->success("Memcached cleared");

        return Command::SUCCESS;
    }
}
