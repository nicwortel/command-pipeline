<?php
declare(strict_types=1);

namespace NicWortel\CommandPipeline\EventHandling;

interface BufferedEventBus
{
    public function flush(): void;
}
