<?php

declare(strict_types=1);

namespace App\Service\Reader;

use App\Dto\TransactionDto;
use Generator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\SerializerInterface;

readonly class TransactionsFileReader implements TransactionsReaderInterface
{
    /**
     * @param Filesystem $filesystem
     * @param SerializerInterface $serializer
     */
    public function __construct(
        private Filesystem $filesystem,
        private SerializerInterface $serializer
    ) {
    }

    /**
     * @param string $filename
     * @return Generator
     */
    public function getTransactionContent(string $filename): Generator
    {
        $contents = $this->filesystem->readFile($filename);

        foreach (explode("\n", $contents) as $row) {
            yield $this->serializer->deserialize($row, TransactionDto::class, 'json');
        }
    }
}