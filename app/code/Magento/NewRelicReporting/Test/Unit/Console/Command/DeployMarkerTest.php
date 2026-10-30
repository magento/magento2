<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\NewRelicReporting\Test\Unit\Console\Command;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\NewRelicReporting\Console\Command\DeployMarker;
use Magento\NewRelicReporting\Model\Apm\Deployments;
use Magento\NewRelicReporting\Model\Apm\DeploymentsFactory;
use Magento\NewRelicReporting\Model\Config;
use Magento\NewRelicReporting\Model\ServiceShellUser;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * Test for DeployMarker console command
 */
class DeployMarkerTest extends TestCase
{
    /**
     * @var DeploymentsFactory|MockObject
     */
    private $deploymentsFactoryMock;

    /**
     * @var Deployments|MockObject
     */
    private $deploymentMock;

    /**
     * @var Config|MockObject
     */
    private $configMock;

    /**
     * @var ServiceShellUser|MockObject
     */
    private $serviceShellUserMock;

    /**
     * @var DeployMarker
     */
    private $command;

    /**
     * @var InputInterface|MockObject
     */
    private $inputMock;

    /**
     * @var BufferedOutput
     */
    private $output;

    protected function setUp(): void
    {
        $this->deploymentsFactoryMock = $this->createMock(DeploymentsFactory::class);
        $this->deploymentMock = $this->createMock(Deployments::class);
        $this->configMock = $this->createMock(Config::class);
        $this->serviceShellUserMock = $this->createMock(ServiceShellUser::class);
        $this->inputMock = $this->createMock(InputInterface::class);
        $this->output = new BufferedOutput();

        // Default: NewRelic is enabled (most tests expect this)
        $this->configMock->method('isNewRelicEnabled')->willReturn(true);

        $this->deploymentsFactoryMock->method('create')->willReturn($this->deploymentMock);
        $this->command = new DeployMarker(
            $this->deploymentsFactoryMock,
            $this->serviceShellUserMock,
            $this->configMock
        );
    }

    /**
     * Helper method to mock input arguments and options
     */
    private function mockArguments(array $args, array $options = []): void
    {
        $this->inputMock->method('getArgument')->willReturnMap($args);
        $this->inputMock->method('getOption')->willReturnMap($options);
    }

    /**
     * Test command configuration
     */
    public function testConfigure()
    {
        $this->assertEquals('newrelic:create:deploy-marker', $this->command->getName());
        $this->assertStringContainsString('deployment marker', $this->command->getDescription());

        $definition = $this->command->getDefinition();

        // Check required argument
        $this->assertTrue($definition->hasArgument('message'));
        $this->assertTrue($definition->getArgument('message')->isRequired());

        // Check optional arguments
        $this->assertTrue($definition->hasArgument('change_log'));
        $this->assertTrue($definition->getArgument('change_log')->isRequired());

        $this->assertTrue($definition->hasArgument('user'));
        $this->assertFalse($definition->getArgument('user')->isRequired());

        $this->assertTrue($definition->hasArgument('revision'));
        $this->assertFalse($definition->getArgument('revision')->isRequired());

        // Check options
        $this->assertTrue($definition->hasOption('commit'));
        $this->assertTrue($definition->hasOption('deep-link'));
        $this->assertTrue($definition->hasOption('group-id'));
    }

    /**
     * Test successful execution with string result
     */
    public function testExecuteSuccessWithStringResult()
    {
        $message = 'Test deployment message';
        $user = 'deploy-user';
        $revision = 'v1.0.0';
        $commit = 'abc123';
        $deepLink = 'https://github.com/test/releases/v1.0.0';
        $groupId = 'staging';

        $this->serviceShellUserMock->expects($this->once())
            ->method('get')
            ->with($user)
            ->willReturn($user);

        $this->mockArguments([
            ['message', $message],
            ['changelog', null],
            ['user', $user],
            ['revision', $revision]
        ], [
            ['commit', $commit],
            ['deep-link', $deepLink],
            ['group-id', $groupId]
        ]);

        $this->deploymentMock->expects($this->once())
            ->method('setDeployment')
            ->with($message, false, $user, $revision, $commit, $deepLink, $groupId)
            ->willReturn('Success response');

        $result = $this->command->run($this->inputMock, $this->output);

        $this->assertEquals(0, $result);
        $outputContent = $this->output->fetch();
        $this->assertMatchesRegularExpression(
            '/(✓|OK) NewRelic deployment marker created successfully!/',
            $outputContent
        );
    }

    /**
     * Test successful execution with array result (NerdGraph style)
     */
    public function testExecuteSuccessWithArrayResultNerdGraphStyle()
    {
        $message = 'Test NerdGraph deployment';
        $user = 'deploy-user';
        $revision = 'v2.0.0';
        $commit = 'def456';
        $deepLink = 'https://github.com/test/releases/v2.0.0';
        $groupId = 'staging';

        // Mock deployment result from NerdGraph
        $deploymentResult = [
            'deploymentId' => '12345678-1234-1234-1234-123456789012',
            'entityGuid' => 'TEST_ENTITY_GUID',
            'version' => $revision,
            'description' => $message,
            'user' => $user,
            'timestamp' => 1234567890000, // Feb 13, 2009 23:31:30 UTC
            'changelog' => 'New features',
            'commit' => $commit,
            'deepLink' => $deepLink,
            'groupId' => $groupId
        ];

        $this->serviceShellUserMock->expects($this->once())
            ->method('get')
            ->with($user)
            ->willReturn($user);

        $this->mockArguments([
            ['message', $message],
            ['change_log', 'New features'],
            ['user', $user],
            ['revision', $revision]
        ], [
            ['commit', $commit],
            ['deep-link', $deepLink],
            ['group-id', $groupId]
        ]);

        $this->deploymentMock->expects($this->once())
            ->method('setDeployment')
            ->with($message, 'New features', $user, $revision, $commit, $deepLink, $groupId)
            ->willReturn($deploymentResult);

        $result = $this->command->run($this->inputMock, $this->output);

        $this->assertEquals(0, $result);
        $outputContent = $this->output->fetch();

        // Group related assertions
        $expectedStrings = [$message, $user, $commit, $deepLink, $groupId];
        foreach ($expectedStrings as $expected) {
            $this->assertStringContainsString($expected, $outputContent);
        }

        $this->assertMatchesRegularExpression(
            '/(✓|OK) NewRelic deployment marker created successfully!/',
            $outputContent
        );
        $this->assertMatchesRegularExpression('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $outputContent);
    }

    /**
     * Test successful deployment with minimal parameters
     */
    public function testExecuteSuccessWithMinimalParameters()
    {
        $message = 'Minimal deployment';

        $this->serviceShellUserMock->expects($this->once())
            ->method('get')
            ->with(null)
            ->willReturn('');

        $this->mockArguments([
            ['message', $message],
            ['changelog', null],
            ['user', null],
            ['revision', null]
        ], [
            ['commit', null],
            ['deep-link', null],
            ['group-id', null]
        ]);

        $this->deploymentMock->expects($this->once())
            ->method('setDeployment')
            ->with($message, false, false, null, null, null, null)
            ->willReturn('Success');

        $result = $this->command->run($this->inputMock, $this->output);

        $this->assertEquals(0, $result);
        $outputContent = $this->output->fetch();
        $this->assertMatchesRegularExpression(
            '/(✓|OK) NewRelic deployment marker created successfully!/',
            $outputContent
        );
    }

    /**
     * Test execution failure with false return
     */
    public function testExecuteFailureWithFalseReturn()
    {
        $message = 'Failed deployment';

        $this->serviceShellUserMock->expects($this->once())
            ->method('get')
            ->with(null)
            ->willReturn('');

        $this->mockArguments([
            ['message', $message],
            ['changelog', null],
            ['user', null],
            ['revision', null]
        ], [
            ['commit', null],
            ['deep-link', null],
            ['group-id', null]
        ]);

        $this->deploymentMock->expects($this->once())
            ->method('setDeployment')
            ->willReturn(false);

        $result = $this->command->run($this->inputMock, $this->output);

        $this->assertEquals(1, $result);
        $outputContent = $this->output->fetch();
        $this->assertMatchesRegularExpression('/(✗|ERROR) Failed to create deployment marker/', $outputContent);
    }

    /**
     * Test execution with LocalizedException
     */
    public function testExecuteWithLocalizedException()
    {
        $message = 'Test deployment';
        $exceptionMessage = 'Configuration error';

        $this->serviceShellUserMock->expects($this->once())
            ->method('get')
            ->willReturn('');

        $this->mockArguments([
            ['message', $message],
            ['changelog', null],
            ['user', null],
            ['revision', null]
        ], [
            ['commit', null],
            ['deep-link', null],
            ['group-id', null]
        ]);

        $this->deploymentMock->expects($this->once())
            ->method('setDeployment')
            ->willThrowException(new LocalizedException(__($exceptionMessage)));

        $result = $this->command->run($this->inputMock, $this->output);

        $this->assertEquals(1, $result);
        $outputContent = $this->output->fetch();

        // Group related assertions - check for actual error format from DeployMarker command
        $expectedStrings = ['✗ Error:', $exceptionMessage];
        foreach ($expectedStrings as $expected) {
                $this->assertStringContainsString($expected, $outputContent);
        }
        $this->assertMatchesRegularExpression('/(✗|ERROR)/', $outputContent);
    }

    /**
     * Test execution with generic Exception
     */
    public function testExecuteWithGenericException()
    {
        $message = 'Test deployment';
        $exceptionMessage = 'Unexpected error';

        $this->serviceShellUserMock->expects($this->once())
            ->method('get')
            ->willReturn('');

        $this->mockArguments([
            ['message', $message],
            ['changelog', null],
            ['user', null],
            ['revision', null]
        ], [
            ['commit', null],
            ['deep-link', null],
            ['group-id', null]
        ]);

        $this->deploymentMock->expects($this->once())
            ->method('setDeployment')
            ->willThrowException(new Exception($exceptionMessage));

        $result = $this->command->run($this->inputMock, $this->output);

        $this->assertEquals(1, $result);
        $outputContent = $this->output->fetch();
        $this->assertMatchesRegularExpression(
            '/(✗|ERROR) Error: ' . preg_quote($exceptionMessage, '/') . '/',
            $outputContent
        );
    }

    /**
     * Test successful execution with partial deployment data (v2 REST API style)
     */
    public function testExecuteSuccessWithPartialDeploymentDataV2RestStyle()
    {
        $message = 'Test deployment';

        // Mock deployment result with partial data (v2 REST API response style)
        $deploymentResult = [
            'deploymentId' => 'test-deployment-id',
            'entityGuid' => 'test-entity-guid',
            'version' => 'v1.0.0',
            'description' => $message,
            'user' => 'deploy-user',
            'timestamp' => 1609459200000 // 2021-01-01 00:00:00 UTC
        ];

        $this->serviceShellUserMock->expects($this->once())
            ->method('get')
            ->with(null)
            ->willReturn('');

        $this->mockArguments([
            ['message', $message],
            ['changelog', null],
            ['user', null],
            ['revision', 'v1.0.0']
        ], [
            ['commit', null],
            ['deep-link', null],
            ['group-id', null]
        ]);

        $this->deploymentMock->expects($this->once())
            ->method('setDeployment')
            ->with($message, false, false, 'v1.0.0', null, null, null)
            ->willReturn($deploymentResult);

        $result = $this->command->run($this->inputMock, $this->output);

        $this->assertEquals(0, $result);
        $outputContent = $this->output->fetch();

        // Group basic deployment data assertions
        $expectedStrings = ['test-deployment-id', 'test-entity-guid', 'v1.0.0', $message, 'deploy-user'];
        foreach ($expectedStrings as $expected) {
            $this->assertStringContainsString($expected, $outputContent);
        }

        $this->assertMatchesRegularExpression(
            '/(✓|OK) NewRelic deployment marker created successfully!/',
            $outputContent
        );
        $this->assertMatchesRegularExpression('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $outputContent);

        // Should show N/A for missing optional fields
        $excludedFields = ['changelog', 'Commit', 'Deep Link', 'Group ID'];
        foreach ($excludedFields as $field) {
            $this->assertStringNotContainsString($field, $outputContent);
        }
    }

    /**
     * Test successful execution with complete deployment data (NerdGraph style)
     */
    public function testExecuteSuccessWithCompleteDeploymentDataNerdGraphStyle()
    {
        $message = 'Complete deployment';

        // Mock deployment result with complete data (NerdGraph response style)
        $deploymentResult = [
            'deploymentId' => 'complete-deployment-id',
            'entityGuid' => 'complete-entity-guid',
            'version' => 'v2.0.0',
            'description' => $message,
            'user' => 'complete-user',
            'timestamp' => 1640995200000, // 2022-01-01 00:00:00 UTC
            'changelog' => 'Added new features',
            'commit' => 'abc123def456',
            'deepLink' => 'https://github.com/test/releases/v2.0.0',
            'groupId' => 'production'
        ];

        $this->serviceShellUserMock->expects($this->once())
            ->method('get')
            ->with('complete-user')
            ->willReturn('complete-user');

        $this->mockArguments([
            ['message', $message],
            ['change_log', 'Added new features'],
            ['user', 'complete-user'],
            ['revision', 'v2.0.0']
        ], [
            ['commit', 'abc123def456'],
            ['deep-link', 'https://github.com/test/releases/v2.0.0'],
            ['group-id', 'production']
        ]);

        $this->deploymentMock->expects($this->once())
            ->method('setDeployment')
            ->with(
                $message,
                'Added new features',
                'complete-user',
                'v2.0.0',
                'abc123def456',
                'https://github.com/test/releases/v2.0.0',
                'production'
            )
            ->willReturn($deploymentResult);

        $result = $this->command->run($this->inputMock, $this->output);

        $this->assertEquals(0, $result);
        $outputContent = $this->output->fetch();

        // Group complete deployment data assertions
        $expectedData = [
            'complete-deployment-id', 'complete-entity-guid', 'v2.0.0',
            $message, 'Added new features', 'complete-user', 'abc123def456',
            'https://github.com/test/releases/v2.0.0', 'production'
        ];
        foreach ($expectedData as $expected) {
            $this->assertStringContainsString($expected, $outputContent);
        }

        $this->assertMatchesRegularExpression(
            '/(✓|OK) NewRelic deployment marker created successfully!/',
            $outputContent
        );
        $this->assertMatchesRegularExpression('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $outputContent);

        // Should show all optional fields
        $requiredFields = ['Change log', 'Commit', 'Deep Link', 'Group ID'];
        foreach ($requiredFields as $field) {
            $this->assertStringContainsString($field, $outputContent);
        }
    }

    /**
     * Test command with null timestamp
     */
    public function testExecuteSuccessWithNullTimestamp()
    {
        $message = 'Test deployment';

        // Mock deployment result with null timestamp
        $deploymentData = [
            'deploymentId' => 'test-deployment-id',
            'entityGuid' => 'test-entity-guid',
            'version' => 'v1.0.0',
            'description' => $message,
            'user' => 'test-user',
            'timestamp' => null
        ];

        $this->serviceShellUserMock->expects($this->once())
            ->method('get')
            ->with(null)
            ->willReturn(null);

        $this->mockArguments([
            ['message', $message],
            ['changelog', null],
            ['user', null],
            ['revision', 'v1.0.0']
        ], [
            ['commit', null],
            ['deep-link', null],
            ['group-id', null]
        ]);

        $this->deploymentMock->expects($this->once())
            ->method('setDeployment')
            ->with(
                $message,
                false,
                false,
                'v1.0.0',
                null,
                null,
                null
            )
            ->willReturn($deploymentData);

        $result = $this->command->run($this->inputMock, $this->output);

        $this->assertEquals(0, $result);
        $outputContent = $this->output->fetch();
        $this->assertMatchesRegularExpression(
            '/(✓|OK) NewRelic deployment marker created successfully!/',
            $outputContent
        );
        $this->assertStringContainsString('N/A', $outputContent); // For null timestamp
    }

    /**
     * Test command argument validation
     */
    public function testCommandArgumentValidation()
    {
        // Test that the command is properly configured with required argument
        $definition = $this->command->getDefinition();

        // Verify the message argument exists and is required
        $this->assertTrue($definition->hasArgument('message'));
        $messageArg = $definition->getArgument('message');
        $this->assertTrue($messageArg->isRequired());
        $this->assertEquals('Deploy Message / Description', $messageArg->getDescription());

        // Verify optional arguments exist
        $this->assertTrue($definition->hasArgument('change_log'));
        $this->assertTrue($definition->hasArgument('user'));
        $this->assertTrue($definition->hasArgument('revision'));

        // Verify options exist
        $this->assertTrue($definition->hasOption('commit'));
        $this->assertTrue($definition->hasOption('deep-link'));
        $this->assertTrue($definition->hasOption('group-id'));
    }

    /**
     * Test execution with empty message
     */
    public function testExecuteWithEmptyMessage()
    {
        $message = '';

        $this->serviceShellUserMock->expects($this->once())
            ->method('get')
            ->with(null)
            ->willReturn('');

        $this->mockArguments([
            ['message', $message],
            ['changelog', null],
            ['user', null],
            ['revision', null]
        ], [
            ['commit', null],
            ['deep-link', null],
            ['group-id', null]
        ]);

        $this->deploymentMock->expects($this->once())
            ->method('setDeployment')
            ->with($message, false, false, null, null, null, null)
            ->willReturn('Success');

        $result = $this->command->run($this->inputMock, $this->output);

        // Should process empty message (validation handled by deployment service)
        $this->assertEquals(0, $result);
        $outputContent = $this->output->fetch();
        $this->assertMatchesRegularExpression(
            '/(✓|OK) NewRelic deployment marker created successfully!/',
            $outputContent
        );
    }

    /**
     * Test execution when NewRelic is disabled
     */
    public function testExecuteWithDisabledNewRelic()
    {
        $message = 'Test deployment message';

        $disabledConfig = $this->createMock(Config::class);
        $disabledConfig->method('isNewRelicEnabled')->willReturn(false);

        // Create command with disabled config
        $disabledCommand = new DeployMarker(
            $this->deploymentsFactoryMock,
            $this->serviceShellUserMock,
            $disabledConfig
        );

        $this->mockArguments([
            ['message', $message],
            ['changelog', null],
            ['user', null],
            ['revision', null]
        ], [
            ['commit', null],
            ['deep-link', null],
            ['group-id', null]
        ]);

        // Should not call setDeployment when disabled
        $this->deploymentMock->expects($this->never())
            ->method('setDeployment');

        $result = $disabledCommand->run($this->inputMock, $this->output);

        $this->assertEquals(1, $result);
        $outputContent = $this->output->fetch();
        $this->assertMatchesRegularExpression('/(✗|ERROR) New Relic is not enabled/', $outputContent);
    }
}
