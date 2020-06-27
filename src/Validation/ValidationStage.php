<?php

declare(strict_types=1);

namespace NicWortel\CommandPipeline\Validation;

use NicWortel\CommandPipeline\Stage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use function array_reduce;
use function count;
use function get_class;
use function iterator_to_array;

final class ValidationStage implements Stage
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(ValidatorInterface $validator, LoggerInterface $logger)
    {
        $this->validator = $validator;
        $this->logger = $logger;
    }

    public function process(object $command): object
    {
        $violations = $this->validator->validate($command);
        $violations = $this->normalizeViolations($violations);

        if (count($violations) > 0) {
            $this->logger->error(
                'The command is invalid, aborting processing.',
                ['command' => get_class($command), 'violations' => $violations]
            );

            throw new InvalidCommandException($violations);
        }

        $this->logger->debug('The command passed validation.');

        return $command;
    }

    /**
     * @param ConstraintViolationListInterface<ConstraintViolationInterface> $violations
     * @return string[][]
     */
    private function normalizeViolations(ConstraintViolationListInterface $violations): array
    {
        return array_reduce(
            iterator_to_array($violations),
            function (array $violations, ConstraintViolationInterface $violation): array {
                $violations[$violation->getPropertyPath()][] = $violation->getMessage();

                return $violations;
            },
            []
        );
    }
}
