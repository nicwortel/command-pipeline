<?php
declare(strict_types=1);

namespace NicWortel\CommandPipeline;

final class StagedPipeline implements CommandPipeline
{
    /**
     * @var Stage[]
     */
    private $stages;

    /**
     * @param Stage[] $stages
     */
    public function __construct(array $stages)
    {
        $this->stages = $stages;
    }

    /**
     * @inheritdoc
     */
    public function process($command): void
    {
        foreach ($this->stages as $stage) {
            $command = $stage->process($command);
        }
    }
}
