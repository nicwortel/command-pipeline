<?php
declare(strict_types=1);

namespace NicWortel\CommandPipeline;

use Assert\Assertion;
use Psr\Log\LoggerInterface;
use function get_class;

final class StagedPipeline implements CommandPipeline
{
    /**
     * @var Stage[]
     */
    private $stages;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Stage[]         $stages
     * @param LoggerInterface $logger
     */
    public function __construct(array $stages, LoggerInterface $logger)
    {
        Assertion::allIsInstanceOf($stages, Stage::class);

        $this->stages = $stages;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function process(object $command): void
    {
        $commandName = get_class($command);

        $this->logger->debug('Started processing a command', ['command' => $commandName]);

        foreach ($this->stages as $stage) {
            $command = $stage->process($command);
        }

        $this->logger->debug('Finished processing a command', ['command' => $commandName]);
    }
}
