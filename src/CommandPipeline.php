<?php
declare(strict_types=1);

namespace NicWortel\CommandPipeline;

interface CommandPipeline
{
    /**
     * @param object $command
     *
     * @return void
     */
    public function process($command): void;
}
