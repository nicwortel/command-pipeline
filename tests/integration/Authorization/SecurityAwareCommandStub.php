<?php
declare(strict_types=1);

namespace NicWortel\CommandPipeline\Tests\Integration\Authorization;

use NicWortel\CommandPipeline\Authorization\SecurityAwareCommand;

final class SecurityAwareCommandStub implements SecurityAwareCommand
{
    /**
     * @var string[]
     */
    private static $allowedRoles = [];

    /**
     * @param string[] $allowedRoles
     */
    public static function setAllowedRoles(array $allowedRoles): void
    {
        self::$allowedRoles = $allowedRoles;
    }

    /**
     * @inheritDoc
     */
    public static function getRolesAllowedToExecuteCommand(): array
    {
        return self::$allowedRoles;
    }
}
