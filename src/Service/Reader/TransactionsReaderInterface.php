<?php

declare(strict_types=1);

namespace App\Service\Reader;

use Generator;

interface TransactionsReaderInterface
{
    /**
     * @param string $filename
     * @return Generator
     */
    public function getTransactionContent(string $filename): Generator;
}