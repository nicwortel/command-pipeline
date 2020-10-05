<?php

declare(strict_types=1);

namespace NicWortel\CommandPipeline;

use Assert\Assertion;
use Psr\Log\LoggerInterface;

use function get_class;
use function sprintf;

final class StagedPipeline implements CommandPipeline
{
    /**
     * @var Stage[]
     */
    private array $stages;

    private LoggerInterface $logger;

    /**
     * @param Stage[] $stages
     */
    public function __construct(array $stages, LoggerInterface $logger)
    {
        Assertion::allIsInstanceOf($stages, Stage::class);

        $this->stages = $stages;
        $this->logger = $logger;
    }

    public function process(object $command): void
    {
        $commandName = get_class($command);

        $this->logger->debug(sprintf('The command pipeline started processing a new "%s" command.', $commandName));

        foreach ($this->stages as $stage) {
            $command = $stage->process($command);
        }

        $this->logger->debug(sprintf('The command pipeline successfully processed the "%s" command.', $commandName));
    }
}
