<?php

declare(strict_types=1);

namespace NicWortel\CommandPipeline;

interface CommandPipeline
{
    public function process(object $command): void;
}
