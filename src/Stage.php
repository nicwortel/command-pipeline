<?php

declare(strict_types=1);

namespace NicWortel\CommandPipeline;

interface Stage
{
    public function process(object $command): object;
}
