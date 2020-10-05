<?php

declare(strict_types=1);

namespace NicWortel\CommandPipeline\Validation;

use Exception;

use function array_keys;

final class InvalidCommandException extends Exception
{
    /**
     * @var string[][]
     */
    private array $violations;

    /**
     * @param string[][] $violations
     */
    public function __construct(array $violations, string $message = 'The provided command is invalid')
    {
        parent::__construct($message);

        $this->violations = $violations;
    }

    /**
     * @return string[]
     */
    public function getInvalidProperties(): array
    {
        return array_keys($this->violations);
    }

    /**
     * @return string[]
     */
    public function getPropertyViolations(string $property): array
    {
        return $this->violations[$property];
    }
}
