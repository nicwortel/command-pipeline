<?php
declare(strict_types=1);

namespace NicWortel\CommandPipeline\Authorization;

use NicWortel\CommandPipeline\Stage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use function get_class;
use function sprintf;
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
            $this->logger->debug(
                'The command does not implement the SecurityAwareCommand interface, skipping authorization check.'
            );

            return $command;
        }

        if (PHP_SAPI === 'cli' && $this->tokenStorage->getToken() === null) {
            $this->logger->debug(
                'The command pipeline is executed from the CLI and there is no authenticated user,' .
                ' skipping authorization check.'
            );

            return $command;
        }

        foreach ($command->getRolesAllowedToExecuteCommand() as $role) {
            if ($this->authorizationChecker->isGranted($role)) {
                $this->logger->debug('The authenticated user is authorized to execute the command, continuing.');

                return $command;
            }
        }

        $token = $this->tokenStorage->getToken();

        $this->logger->error(
            sprintf(
                'The current user does not have (one of) the role(s) required to execute the "%s" command, ' .
                'aborting processing.',
                get_class($command)
            ),
            [
                'username' => $token ? $token->getUsername() : null,
                'user_roles' => $token ? $token->getRoleNames() : null,
                'allowed_roles' => $command->getRolesAllowedToExecuteCommand(),
            ]
        );

        throw ForbiddenException::forCommand(get_class($command));
    }
}
