<?php
declare(strict_types=1);

namespace NicWortel\CommandPipeline\Tests\Integration\Validation;

use Symfony\Component\Validator\Constraints as Assert;

final class CommandStub
{
    /**
     * @var string
     *
     * @Assert\Email()
     */
    public $emailAddress;
}
