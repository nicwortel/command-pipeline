<?php
declare(strict_types=1);

namespace NicWortel\CommandPipeline\Tests\Integration\Validation;

use Doctrine\Common\Annotations\AnnotationRegistry;
use NicWortel\CommandPipeline\Validation\InvalidCommandException;
use NicWortel\CommandPipeline\Validation\ValidationStage;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Psr\Log\Test\TestLogger;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ValidationStageTest extends TestCase
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    protected function setUp(): void
    {
        AnnotationRegistry::registerLoader('class_exists');
        $this->validator = Validation::createValidatorBuilder()->enableAnnotationMapping()->getValidator();
    }

    public function testReturnsTheCommandIfItIsValid(): void
    {
        $stage = new ValidationStage($this->validator, new NullLogger());

        $command = new CommandStub();

        $output = $stage->process($command);

        $this->assertEquals($command, $output);
    }

    public function testThrowsAnExceptionIfTheCommandIsInvalid(): void
    {
        $stage = new ValidationStage($this->validator, new NullLogger());

        $command = new CommandStub();
        $command->emailAddress = 'invalid';

        $this->expectException(InvalidCommandException::class);

        $stage->process($command);
    }

    public function testReturnsViolationsInTheException(): void
    {
        $stage = new ValidationStage($this->validator, new NullLogger());

        $command = new CommandStub();
        $command->emailAddress = 'invalid';

        try {
            $stage->process($command);
        } catch (InvalidCommandException $exception) {
            $this->assertSame(['emailAddress'], $exception->getInvalidProperties());
            $this->assertSame(
                ['This value is not a valid email address.'],
                $exception->getPropertyViolations('emailAddress')
            );
        }
    }

    public function testLogsInvalidCommands(): void
    {
        $logger = new TestLogger();
        $stage = new ValidationStage($this->validator, $logger);

        $command = new CommandStub();
        $command->emailAddress = 'invalid';

        try {
            $stage->process($command);
        } catch (InvalidCommandException $exception) {
        }

        $this->assertTrue(
            $logger->hasError(
                [
                    'message' => 'The command is invalid, aborting processing.',
                    'context' => [
                        'command' => CommandStub::class,
                        'violations' => ['emailAddress' => ['This value is not a valid email address.']],
                    ],
                ]
            )
        );
    }
}
