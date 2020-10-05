<?php

declare(strict_types=1);

namespace NicWortel\CommandPipeline\Tests\Integration\Validation;

use Symfony\Component\Validator\Constraints as Assert;

final class CommandStub
{
    /**
     *
     * @Assert\Email()
     */
    public string $emailAddress;
}
