<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\NewRelicReporting\Console\Command;

use Magento\Framework\ObjectManagerInterface;
use Magento\NewRelicReporting\Console\Command\DeployMarker;
use Magento\TestFramework\Fixture\Config as ConfigFixture;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Integration test for DeployMarker console command.
 * Covers framework integration (DI, Config, CLI) for both v2_rest and NerdGraph modes.
 *
 * @magentoAppIsolation enabled
 */
class DeployMarkerCommandTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var DeployMarker
     */
    private $command;

    /**
     * @var CommandTester
     */
    private $commandTester;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->command = $this->objectManager->get(DeployMarker::class);
        $this->commandTester = new CommandTester($this->command);
    }

    /**
     * Test command with missing required argument
     */
    public function testCommandWithMissingArgument()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not enough arguments (missing: "message, change_log")');

        $this->commandTester->execute([]);
    }

    /**
     * Test command with valid arguments but disabled NewRelic
     */
    #[ConfigFixture('newrelicreporting/general/enable', '0')]
    public function testCommandWithDisabledNewRelic()
    {
        $exitCode = $this->commandTester->execute([
            'message' => 'Test deployment',
            'change_log' => 'Test changelog'
        ]);

        $this->assertEquals(1, $exitCode);
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString(
            '✗ New Relic is not enabled. Please check your configuration.',
            $output
        );
    }

    /**
     * Test command with minimal arguments (v2 REST mode)
     */
    #[ConfigFixture('newrelicreporting/general/enable', '1')]
    #[ConfigFixture('newrelicreporting/general/api_mode', 'v2_rest')]
    #[ConfigFixture('newrelicreporting/general/app_id', '12345')]
    #[ConfigFixture('newrelicreporting/general/api', 'fake_api_key_for_testing')]
    public function testCommandWithMinimalArgumentsV2Rest()
    {
        $exitCode = $this->commandTester->execute([
            'message' => 'Test deployment message',
            'change_log' => 'Test changelog'
        ]);

        $this->assertTrue(
            in_array($exitCode, [0, 1], true),
            'Command should exit with 0 (success) or 1 (graceful failure)'
        );
        $output = $this->commandTester->getDisplay();

        // No fatal framework errors
        $this->assertStringNotContainsString('Fatal error', $output);
        $this->assertStringNotContainsString('Call to a member function', $output);

        $this->assertMatchesRegularExpression(
            '/(✓|✗)/',
            $output,
            'Output should contain success (✓) or error (✗) indicator'
        );
    }

    /**
     * Test command configuration, help, and CLI framework integration
     */
    public function testCommandConfiguration()
    {
        // Test command identity
        $this->assertEquals('newrelic:create:deploy-marker', $this->command->getName());

        // Test description and help
        $description = $this->command->getDescription();
        $this->assertIsString($description);
        $this->assertStringContainsString('deployment marker', $description);

        $help = $this->command->getHelp();
        $this->assertIsString($help);

        // Test Symfony CLI framework integration (arguments, options registration)
        $definition = $this->command->getDefinition();

        // Verify required argument
        $this->assertTrue($definition->hasArgument('message'));
        $messageArg = $definition->getArgument('message');
        $this->assertTrue($messageArg->isRequired());

        // Verify optional arguments
        $this->assertTrue($definition->hasArgument('change_log'));
        $this->assertTrue($definition->hasArgument('user'));
        $this->assertTrue($definition->hasArgument('revision'));

        // Verify options
        $this->assertTrue($definition->hasOption('commit'));
        $this->assertTrue($definition->hasOption('deep-link'));
        $this->assertTrue($definition->hasOption('group-id'));
    }

    /**
     * Test command with all arguments and options (NerdGraph mode)
     */
    #[ConfigFixture('newrelicreporting/general/enable', '1')]
    #[ConfigFixture('newrelicreporting/general/api_mode', 'nerdgraph')]
    #[ConfigFixture('newrelicreporting/general/entity_guid', 'fake-guid-for-testing')]
    #[ConfigFixture('newrelicreporting/general/nerd_graph_api_url', 'https://api.newrelic.com/graphql')]
    #[ConfigFixture('newrelicreporting/general/api', 'fake_api_key_for_testing')]
    public function testCommandWithAllParametersNerdGraph()
    {
        $exitCode = $this->commandTester->execute([
            'message' => 'Full deployment test',
            'change_log' => 'Added new features',
            'user' => 'deploy-user',
            'revision' => 'v2.0.0',
            '--commit' => 'abc123',
            '--deep-link' => 'https://github.com/test/releases/v2.0.0',
            '--group-id' => 'production'
        ]);

        // Framework integration: Should handle gracefully (success or graceful failure)
        $this->assertTrue(
            in_array($exitCode, [0, 1], true),
            'Command should exit with 0 (success) or 1 (graceful failure)'
        );
        $output = $this->commandTester->getDisplay();

        $this->assertStringNotContainsString('Fatal error', $output);
        $this->assertStringNotContainsString('Call to a member function', $output);
        $this->assertMatchesRegularExpression(
            '/(✓|✗)/',
            $output,
            'Output should contain success (✓) or error (✗) indicator'
        );
    }

    /**
     * Test command with empty message
     */
    #[ConfigFixture('newrelicreporting/general/enable', '1')]
    public function testCommandWithEmptyMessage()
    {
        $exitCode = $this->commandTester->execute([
            'message' => '',
            "change_log" => "Test changelog"
        ]);

        $this->assertIsInt($exitCode);
        $output = $this->commandTester->getDisplay();
        $this->assertStringNotContainsString('Fatal error', $output);

        $this->assertMatchesRegularExpression(
            '/(✓|✗)/',
            $output,
            'Output should contain success (✓) or error (✗) indicator'
        );
    }

    /**
     * Test --help option execution (end-to-end CLI wiring)
     */
    public function testCommandHelpOption()
    {
        // Create a standalone application to test help functionality
        $application = new Application();
        $application->setAutoExit(false);
        $application->add($this->command);

        $input = new ArrayInput(['command' => 'newrelic:create:deploy-marker', '--help' => true]);
        $output = new BufferedOutput();

        $exitCode = $application->run($input, $output);

        $this->assertEquals(0, $exitCode);
        $displayOutput = $output->fetch();
        $this->assertStringContainsString('newrelic:create:deploy-marker', $displayOutput);
        $this->assertStringContainsString('deployment marker', $displayOutput);
        $this->assertStringContainsString('Usage:', $displayOutput);
    }

    /**
     * Ensure v2_rest mode never prints NerdGraph details section
     */
    #[ConfigFixture('newrelicreporting/general/enable', '1')]
    #[ConfigFixture('newrelicreporting/general/api_mode', 'v2_rest')]
    #[ConfigFixture('newrelicreporting/general/app_id', '9999')]
    #[ConfigFixture('newrelicreporting/general/api', 'fake_api_key')]
    public function testV2RestDoesNotShowNerdGraphDetails()
    {
        $exitCode = $this->commandTester->execute([
            'message' => 'v2 rest test',
            'change_log' => 'Test changelog'
        ]);

        $this->assertTrue(in_array($exitCode, [0, 1], true));
        $output = $this->commandTester->getDisplay();
        $this->assertStringNotContainsString('Deployment Details:', $output);
    }

    /**
     * Enabled but missing required config should fail gracefully (v2_rest)
     */
    #[ConfigFixture('newrelicreporting/general/enable', '1')]
    #[ConfigFixture('newrelicreporting/general/api_mode', 'v2_rest')]
    public function testV2RestMisconfiguredFailsGracefully()
    {
        $exitCode = $this->commandTester->execute([
            'message' => 'misconfig v2',
            'change_log' => 'Test changelog'
        ]);

        $this->assertEquals(1, $exitCode);
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('✗ Failed to create deployment marker', $output);
        $this->assertStringNotContainsString('not enabled', strtolower($output));
    }

    /**
     * Enabled but missing required config should fail gracefully (nerdgraph)
     */
    #[ConfigFixture('newrelicreporting/general/enable', '1')]
    #[ConfigFixture('newrelicreporting/general/api_mode', 'nerdgraph')]
    public function testNerdGraphMisconfiguredFailsGracefully()
    {
        $exitCode = $this->commandTester->execute([
            'message' => 'misconfig nerdgraph',
            'change_log' => 'Test changelog'
        ]);

        $this->assertEquals(1, $exitCode);
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('✗ Failed to create deployment marker', $output);
        $this->assertStringNotContainsString('not enabled', strtolower($output));
    }

    /**
     * Validate option aliases (-c, -d, -g) are registered
     */
    public function testOptionAliases()
    {
        $definition = $this->command->getDefinition();

        $this->assertTrue($definition->hasOption('commit'));
        $this->assertEquals('c', $definition->getOption('commit')->getShortcut());

        $this->assertTrue($definition->hasOption('deep-link'));
        $this->assertEquals('d', $definition->getOption('deep-link')->getShortcut());

        $this->assertTrue($definition->hasOption('group-id'));
        $this->assertEquals('g', $definition->getOption('group-id')->getShortcut());
    }
}
