<?php

declare(strict_types=1);

namespace App\Service\Binlist;

use App\Dto\BinDto;
use App\Exception\Service\Binlist\BinlistException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

readonly class BinlistService implements BinServiceInterface
{
    /**
     * @param BinlistSettingsStorageInterface $settingsStorage
     * @param HttpClientInterface $httpClient
     * @param SerializerInterface $serializer
     */
    public function __construct(
        private BinlistSettingsStorageInterface $settingsStorage,
        private HttpClientInterface $httpClient,
        private SerializerInterface $serializer
    ) {
    }

    /**
     * @param string $bin
     * @return BinDto
     * @throws BinlistException
     */
    public function getBinDetails(string $bin): BinDto
    {
        try {
            return $this->getBinDetailsProcess($bin);
        } catch (
            TransportExceptionInterface
            | ClientExceptionInterface
            | RedirectionExceptionInterface
            | ServerExceptionInterface $e
        ) {
            throw new BinlistException(sprintf(
                'Error during getting bin details %s',
                $bin
            ), previous: $e);
        }
    }

    /**
     * @param string $bin
     * @return BinDto
     * @throws BinlistException
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    private function getBinDetailsProcess(string $bin): BinDto
    {
        $url = sprintf('%s/%s', $this->settingsStorage->getBinlistHost(), $bin);

        $response = $this->httpClient->request('GET', $url);

        if ($response->getStatusCode() === 404) {
            throw new BinlistException(sprintf('Bin %s not found', $bin));
        }

        return $this->serializer->deserialize(
            $response->getContent(),
            BinDto::class,
            'json'
        );
    }
}