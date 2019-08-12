<?php
declare(strict_types=1);

namespace NicWortel\CommandPipeline\Tests\Unit\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Mockery;
use NicWortel\CommandPipeline\Doctrine\TransactionalStageDecorator;
use NicWortel\CommandPipeline\Stage;
use NicWortel\CommandPipeline\Tests\Integration\Validation\CommandStub;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Psr\Log\Test\TestLogger;
use Throwable;

class TransactionalStageDecoratorTest extends TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /**
     * @var Mockery\MockInterface|EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var Mockery\MockInterface|Stage
     */
    private $wrappedStage;

    protected function setUp(): void
    {
        $this->entityManager = Mockery::spy(EntityManagerInterface::class);
        $this->wrappedStage = Mockery::spy(Stage::class);
    }

    public function testFlushesTheEntityManagerAfterHandlingACommand(): void
    {
        $stage = new TransactionalStageDecorator($this->entityManager, $this->wrappedStage, new NullLogger());

        $stage->process(new CommandStub());

        $this->entityManager->shouldHaveReceived('flush');
    }

    public function testHandlesTheCommandWithinATransaction(): void
    {
        $pipeline = new TransactionalStageDecorator($this->entityManager, $this->wrappedStage, new NullLogger());

        $pipeline->process(new CommandStub());

        $this->entityManager->shouldHaveReceived('beginTransaction');
        $this->entityManager->shouldHaveReceived('commit');
    }

    public function testRollsBackTheTransactionIfHandlingTheCommandFailed(): void
    {
        $pipeline = new TransactionalStageDecorator($this->entityManager, $this->wrappedStage, new NullLogger());

        $this->wrappedStage->shouldReceive('process')->andThrow(new Exception());

        try {
            $pipeline->process(new CommandStub());
        } catch (Throwable $throwable) {
        }

        $this->entityManager->shouldHaveReceived('beginTransaction');
        $this->entityManager->shouldHaveReceived('rollback');
    }

    public function testLogsErrorWhenHandlingTheCommandHasFailed(): void
    {
        $logger = new TestLogger();
        $pipeline = new TransactionalStageDecorator($this->entityManager, $this->wrappedStage, $logger);

        $this->wrappedStage->shouldReceive('process')->andThrow(new Exception());

        try {
            $pipeline->process(new CommandStub());
        } catch (Throwable $throwable) {
        }

        $this->assertTrue(
            $logger->hasError(
                ['message' => 'An exception was thrown while handling the command, rolling back the transaction']
            )
        );
    }

    public function testRethrowsTheException(): void
    {
        $pipeline = new TransactionalStageDecorator($this->entityManager, $this->wrappedStage, new NullLogger());

        $this->wrappedStage->shouldReceive('process')->andThrow(new Exception());

        $this->expectException(Exception::class);

        $pipeline->process(new CommandStub());
    }
}
