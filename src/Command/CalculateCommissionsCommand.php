<?php

namespace App\Command;

use App\Service\CalculateCommissionService;
use App\Service\Reader\TransactionsReaderInterface;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @see input.txt
 */
#[AsCommand(
    name: 'app:calculate-commissions',
    description: 'Calculate commissions by file with transactions',
)]
class CalculateCommissionsCommand extends Command
{
    /**
     * @param TransactionsReaderInterface $transactionsReader
     * @param CalculateCommissionService $calculateCommissionService
     */
    public function __construct(
        private readonly TransactionsReaderInterface $transactionsReader,
        private readonly CalculateCommissionService $calculateCommissionService
    ) {
        parent::__construct();
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->addArgument(
            'fileName',
            InputArgument::REQUIRED,
            'Filename'
        );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $fileName = $input->getArgument('fileName');

        if (empty($fileName)) {
            throw new Exception('Filename of the file with transactions is not valid');
        }

        foreach ($this->transactionsReader->getTransactionContent($fileName) as $transactionDto) {
            $output->writeln($this->calculateCommissionService->calculate($transactionDto));
        }

        return Command::SUCCESS;
    }
}
