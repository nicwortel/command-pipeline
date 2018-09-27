<?php
declare(strict_types=1);

namespace NicWortel\CommandPipeline;

interface Stage
{
    /**
     * @param object $command
     *
     * @return object
     */
    public function process($command);
}
