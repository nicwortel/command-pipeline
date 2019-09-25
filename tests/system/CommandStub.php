<?php
declare(strict_types=1);

namespace NicWortel\CommandPipeline\Tests\System;

use NicWortel\CommandPipeline\Authorization\SecurityAwareCommand;
use Symfony\Component\Validator\Constraints as Assert;

final class CommandStub implements SecurityAwareCommand
{
    /**
     * @var string
     *
     * @Assert\Email()
     */
    public $emailAddress;

    /**
     * @inheritDoc
     */
    public static function getRolesAllowedToExecuteCommand(): array
    {
        return ['ROLE_ADMIN'];
    }
}
