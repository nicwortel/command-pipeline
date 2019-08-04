<?php
declare(strict_types=1);

namespace NicWortel\CommandPipeline;

use Assert\Assertion;

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
        Assertion::allIsInstanceOf($stages, Stage::class);

        $this->stages = $stages;
    }

    /**
     * @inheritdoc
     */
    public function process(object $command): void
    {
        foreach ($this->stages as $stage) {
            $command = $stage->process($command);
        }
    }
}
