<?php
declare(strict_types=1);

namespace NicWortel\CommandPipeline\Tests\Integration\Authorization;

use NicWortel\CommandPipeline\Authorization\SecurityAwareCommand;

final class SecurityAwareCommandStub implements SecurityAwareCommand
{
    /**
     * @var string[]
     */
    private $allowedRoles;

    /**
     * @param string[] $allowedRoles
     */
    public function __construct(array $allowedRoles)
    {
        $this->allowedRoles = $allowedRoles;
    }

    /**
     * @return string[] A list of roles that are allowed to execute this command. The current user needs to have at
     *                  least one of these roles.
     */
    public function getRolesAllowedToExecuteCommand(): array
    {
        return $this->allowedRoles;
    }
}
