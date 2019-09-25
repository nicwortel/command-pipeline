<?php
declare(strict_types=1);

namespace NicWortel\CommandPipeline\Authorization;

interface SecurityAwareCommand
{
    /**
     * @return string[] A list of role names that are allowed to execute this command. The current user needs to have at
     *                  least one of these roles.
     */
    public static function getRolesAllowedToExecuteCommand(): array;
}
