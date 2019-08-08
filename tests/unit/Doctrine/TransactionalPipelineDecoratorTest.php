<?php
declare(strict_types=1);

namespace NicWortel\CommandPipeline\Tests\Unit\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Mockery;
use NicWortel\CommandPipeline\CommandPipeline;
use NicWortel\CommandPipeline\Doctrine\TransactionalPipelineDecorator;
use NicWortel\CommandPipeline\Tests\Integration\Validation\CommandStub;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Psr\Log\Test\TestLogger;
use Throwable;

class TransactionalPipelineDecoratorTest extends TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /**
     * @var Mockery\MockInterface|EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var Mockery\MockInterface|CommandPipeline
     */
    private $innerPipeline;

    protected function setUp(): void
    {
        $this->entityManager = Mockery::spy(EntityManagerInterface::class);
        $this->innerPipeline = Mockery::spy(CommandPipeline::class);
    }

    public function testFlushesTheEntityManagerAfterHandlingACommand(): void
    {
        $pipeline = new TransactionalPipelineDecorator($this->entityManager, $this->innerPipeline, new NullLogger());

        $pipeline->process(new CommandStub());

        $this->entityManager->shouldHaveReceived('flush');
    }

    public function testHandlesTheCommandWithinATransaction(): void
    {
        $pipeline = new TransactionalPipelineDecorator($this->entityManager, $this->innerPipeline, new NullLogger());

        $pipeline->process(new CommandStub());

        $this->entityManager->shouldHaveReceived('beginTransaction');
        $this->entityManager->shouldHaveReceived('commit');
    }

    public function testRollsBackTheTransactionIfHandlingTheCommandFailed(): void
    {
        $pipeline = new TransactionalPipelineDecorator($this->entityManager, $this->innerPipeline, new NullLogger());

        $this->innerPipeline->shouldReceive('process')->andThrow(new Exception());

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
        $pipeline = new TransactionalPipelineDecorator($this->entityManager, $this->innerPipeline, $logger);

        $this->innerPipeline->shouldReceive('process')->andThrow(new Exception());

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
        $pipeline = new TransactionalPipelineDecorator($this->entityManager, $this->innerPipeline, new NullLogger());

        $this->innerPipeline->shouldReceive('process')->andThrow(new Exception());

        $this->expectException(Exception::class);

        $pipeline->process(new CommandStub());
    }
}
