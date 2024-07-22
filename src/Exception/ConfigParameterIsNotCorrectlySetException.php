<?php

declare(strict_types=1);

namespace App\Exception;

use Exception;
use Throwable;

class ConfigParameterIsNotCorrectlySetException extends Exception
{
    private const string MESSAGE = 'Config parameter %s is not correctly set';

    /**
     * @param string $parameterName
     * @param Throwable|null $previous
     */
    public function __construct(
        private readonly string $parameterName,
        ?Throwable $previous = null,
    ) {
        $message = $this->createMessage();

        parent::__construct($message, previous: $previous);
    }

    /**
     * @return string
     */
    private function createMessage(): string
    {
        return sprintf(
            self::MESSAGE,
            $this->getParameterName(),
        );
    }

    /**
     * @return string
     */
    public function getParameterName(): string
    {
        return $this->parameterName;
    }
}
