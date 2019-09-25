<?php
declare(strict_types=1);

namespace NicWortel\CommandPipeline\Tests\Integration\Authorization;

use Mockery;
use NicWortel\CommandPipeline\Authorization\AuthorizationStage;
use NicWortel\CommandPipeline\Authorization\ForbiddenException;
use NicWortel\CommandPipeline\Tests\Integration\Validation\CommandStub;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManager;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Authorization\Voter\RoleVoter;

class AuthorizationStageTest extends TestCase
{
    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    /**
     * @var AuthorizationChecker
     */
    private $authorizationChecker;

    protected function setUp(): void
    {
        $this->tokenStorage = new TokenStorage();

        $this->authorizationChecker = new AuthorizationChecker(
            $this->tokenStorage,
            Mockery::mock(AuthenticationManagerInterface::class),
            new AccessDecisionManager([new RoleVoter()])
        );
    }

    public function testSkipsCommandsThatAreNotSecurityAware(): void
    {
        $stage = new AuthorizationStage($this->authorizationChecker, new NullLogger());

        $command = new CommandStub();

        $this->assertSame($command, $stage->process($command));
    }

    public function testFailsIfTheUserIsNotAuthorizedToExecuteTheCommand(): void
    {
        $stage = new AuthorizationStage($this->authorizationChecker, new NullLogger());
        $this->tokenStorage->setToken(new UsernamePasswordToken('user', [], 'provider', ['ROLE_USER']));

        SecurityAwareCommandStub::setAllowedRoles(['ROLE_ADMIN']);

        $this->expectException(ForbiddenException::class);

        $stage->process(new SecurityAwareCommandStub());
    }

    public function testReturnsTheCommandIfTheUserIsAuthorizedToExecuteIt(): void
    {
        $stage = new AuthorizationStage($this->authorizationChecker, new NullLogger());
        $this->tokenStorage->setToken(new UsernamePasswordToken('user', [], 'provider', ['ROLE_ADMIN']));

        SecurityAwareCommandStub::setAllowedRoles(['ROLE_ADMIN']);

        $command = new SecurityAwareCommandStub();

        $result = $stage->process($command);

        $this->assertSame($command, $result);
    }

    public function testGrantsAuthorizationIfTheUserHasAtLeastOneOfTheRequiredRoles(): void
    {
        $stage = new AuthorizationStage($this->authorizationChecker, new NullLogger());
        $this->tokenStorage->setToken(new UsernamePasswordToken('user', [], 'provider', ['ROLE_ADMIN']));

        SecurityAwareCommandStub::setAllowedRoles(['ROLE_ADMIN', 'ROLE_OTHER_ROLE']);

        $command = new SecurityAwareCommandStub();

        $result = $stage->process($command);

        $this->assertSame($command, $result);
    }
}
