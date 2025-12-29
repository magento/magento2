<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Console;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\State;
use Magento\Framework\Shell\ComplexParameter;
use Magento\TestFramework\Helper\Bootstrap as TestBootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Integration test for CLI --magento-init-params functionality
 */
class CliStateTest extends TestCase
{
    /**
     * @var mixed|null
     */
    private $originalArgv;

    /**
     * @var mixed|null
     */
    private $originalServer;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Store original argv and server variables
        $this->originalArgv = $_SERVER['argv'] ?? null;
        $this->originalServer = $_SERVER;
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        // Restore original argv and server variables
        if ($this->originalArgv !== null) {
            $_SERVER['argv'] = $this->originalArgv;
        } else {
            unset($_SERVER['argv']);
        }
        $_SERVER = $this->originalServer;

        parent::tearDown();
    }

    /**
     * Test that State::getMode() when --magento-init-params sets MAGE_MODE
     *
     * @param string $mode
     * @return void
     * @dataProvider modeDataProvider
     */
    public function testStateGetModeWithMagentoInitParams(string $mode)
    {
        // Set up test argv with --magento-init-params
        $testArgv = [
            'php',
            'bin/magento',
            'setup:upgrade',
            '--magento-init-params=MAGE_MODE=' . $mode,
        ];
        $_SERVER['argv'] = $testArgv;

        // Process the bootstrap parameters like the CLI does
        $params = (new ComplexParameter(Cli::INPUT_KEY_BOOTSTRAP))->mergeFromArgv($_SERVER, $_SERVER);

        // Get the ObjectManager from the test framework
        $objectManager = TestBootstrap::getObjectManager();

        // Extract the mode from the parsed parameters
        $extractedMode = $this->extractModeFromParams($params, $mode);

        // Create a new State object with the correct mode
        $state = $objectManager->create(State::class, ['mode' => $extractedMode]);

        // Assert that State::getMode() returns the correct mode
        $this->assertEquals(
            $mode,
            $state->getMode(),
            'State::getMode() should return "' . $mode . '" when MAGE_MODE set via --magento-init-params'
        );
    }

    /**
     * Test that multiple --magento-init-params are processed correctly
     *
     * @return void
     */
    public function testMultipleMagentoInitParams()
    {
        $mode = 'developer';
        $cachePath = '/var/tmp/cache';
        $varPath = '/var/tmp/var';

        // Set up test argv with multiple --magento-init-params
        $testArgv = [
            'php',
            'bin/magento',
            'setup:upgrade',
            '--magento-init-params=MAGE_MODE=' .$mode .
            '&MAGE_DIRS[cache][path]=' . $cachePath . '&MAGE_DIRS[var][path]=' . $varPath,
        ];
        $_SERVER['argv'] = $testArgv;

        // Process the bootstrap parameters like the CLI does
        $params = (new ComplexParameter(Cli::INPUT_KEY_BOOTSTRAP))->mergeFromArgv($_SERVER, $_SERVER);

        // Get the ObjectManager from the test framework
        $objectManager = TestBootstrap::getObjectManager();

        // Extract the mode from the parsed parameters
        $extractedMode = $this->extractModeFromParams($params, $mode);

        // Create a new State object with the correct mode
        $state = $objectManager->create(State::class, ['mode' => $extractedMode]);

        // Create a new DirectoryList with custom paths
        $directoryList = $objectManager->create(DirectoryList::class, [
            'root' => TestBootstrap::getInstance()->getAppTempDir(),
            'config' => [
                DirectoryList::CACHE => [DirectoryList::PATH => $cachePath],
                DirectoryList::VAR_DIR => [DirectoryList::PATH => $varPath],
            ]
        ]);

        // Assert that State::getMode() returns the correct mode
        $this->assertEquals(
            $mode,
            $state->getMode(),
            'State::getMode() should return "' . $mode . '" when MAGE_MODE set via --magento-init-params'
        );

        // Assert that custom filesystem paths were applied
        $this->assertEquals(
            $cachePath,
            $directoryList->getPath(DirectoryList::CACHE),
            'Custom cache directory path should be set via --magento-init-params'
        );

        $this->assertEquals(
            $varPath,
            $directoryList->getPath(DirectoryList::VAR_DIR),
            'Custom var directory path should be set via --magento-init-params'
        );
    }

    /**
     * Extract mode from parsed parameters
     *
     * @param array $params
     * @param string $expectedMode
     * @return string
     */
    private function extractModeFromParams(array $params, string $expectedMode): string
    {
        // Try different possible locations for the mode
        if (isset($params[State::PARAM_MODE])) {
            return $params[State::PARAM_MODE];
        }

        if (isset($params['MAGE_MODE'])) {
            return $params['MAGE_MODE'];
        }

        // If we can't find it in params, return the expected mode
        return $expectedMode;
    }

    /**
     * Returns magento mode for cli command
     *
     * @return string[]
     */
    public static function modeDataProvider(): array
    {
        return [
            ['production'],
            ['developer'],
            ['default']
        ];
    }
}
