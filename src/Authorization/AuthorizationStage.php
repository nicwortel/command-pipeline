<?php
declare(strict_types=1);

namespace NicWortel\CommandPipeline\Authorization;

use NicWortel\CommandPipeline\Stage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use function get_class;
use const PHP_SAPI;

final class AuthorizationStage implements Stage
{
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage,
        LoggerInterface $logger
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->tokenStorage = $tokenStorage;
        $this->logger = $logger;
    }

    public function process(object $command): object
    {
        if (!$command instanceof SecurityAwareCommand) {
            return $command;
        }

        if (PHP_SAPI === 'cli' && $this->tokenStorage->getToken() === null) {
            return $command;
        }

        if (!$this->authorizationChecker->isGranted($command->getRolesAllowedToExecuteCommand())) {
            $this->logger->error(
                'User tried to execute command, but does not have the required role(s)',
                [
                    'command' => get_class($command),
                    'allowed_roles' => $command->getRolesAllowedToExecuteCommand(),
                ]
            );

            throw ForbiddenException::forCommand(get_class($command));
        }

        return $command;
    }
}
