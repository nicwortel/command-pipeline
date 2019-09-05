<?php
declare(strict_types=1);

namespace NicWortel\CommandPipeline\Authorization;

use RuntimeException;
use function sprintf;

final class ForbiddenException extends RuntimeException
{
    public static function forCommand(string $commandName): self
    {
        return new self(
            sprintf('The current user does not have the required role(s) to execute command "%s"', $commandName)
        );
    }
}
