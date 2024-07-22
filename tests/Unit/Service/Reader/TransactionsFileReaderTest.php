<?php

namespace App\Tests\Unit\Service\Reader;

use App\Dto\TransactionDto;
use App\Service\Reader\TransactionsFileReader;
use App\Service\Reader\TransactionsReaderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PrinsFrank\Standards\Currency\CurrencyAlpha3;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\SerializerInterface;

class TransactionsFileReaderTest extends TestCase
{
    /** @var MockObject&Filesystem */
    private Filesystem&MockObject $filesystem;

    /** @var MockObject&SerializerInterface */
    private SerializerInterface&MockObject $serializer;

    /** @var TransactionsReaderInterface */
    private TransactionsReaderInterface $service;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->filesystem = $this->createMock(Filesystem::class);
        $this->serializer = $this->createMock(SerializerInterface::class);

        $this->service = new TransactionsFileReader(
            $this->filesystem,
            $this->serializer
        );
    }

    /**
     * @return void
     */
    public function testGetTransactionContentWithExistingFile(): void
    {
        $filename = 'testFile.csv';
        $content = '{"bin": "454455","amount": "250.00","currency": "usd"}';

        $this->filesystem->expects($this->once())
            ->method('readFile')
            ->with($filename)
            ->willReturn($content);

        $transactionDto = new TransactionDto('454455', '250.00', CurrencyAlpha3::US_Dollar);

        $this->serializer->expects($this->once())
            ->method('deserialize')
            ->with($content, TransactionDto::class, 'json')
            ->willReturn($transactionDto);

        $transactions = [...$this->service->getTransactionContent($filename)];

        $this->assertEquals([$transactionDto], $transactions);
    }
}
