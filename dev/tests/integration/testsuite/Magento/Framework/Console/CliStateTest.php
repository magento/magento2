<?php
/**
 * Copyright 2025 Adobe.
 * All rights reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Console;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\State;
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
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Store original argv
        $this->originalArgv = $_SERVER['argv'] ?? null;
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        // Restore original argv
        if ($this->originalArgv !== null) {
            $_SERVER['argv'] = $this->originalArgv;
            $this->originalArgv = null;
        } else {
            unset($_SERVER['argv']);
        }

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

        // Get the State object from the ObjectManager
        $state = $this->getObjectManager()->get(State::class);

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

        // Get the ObjectManager
        $objectManager = $this->getObjectManager();

        // Get the State object from the ObjectManager
        $state = $objectManager->get(State::class);

        // Assert that State::getMode() returns the correct mode
        $this->assertEquals(
            $mode,
            $state->getMode(),
            'State::getMode() should return "' . $mode . '" when MAGE_MODE set via --magento-init-params'
        );

        // Get the DirectoryList to verify filesystem paths were set
        $directoryList = $objectManager->get(DirectoryList::class);

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

    /**
     * Get the ObjectManager from the Cli instance using reflection
     *
     * @return ObjectManager
     */
    private function getObjectManager()
    {
        // Create a new Cli instance
        $cli = new Cli('Magento CLI');

        // Get the ObjectManager from the Cli instance using reflection
        $reflection = new \ReflectionClass($cli);
        $objectManagerProperty = $reflection->getProperty('objectManager');
        $objectManagerProperty->setAccessible(true);
        return $objectManagerProperty->getValue($cli);
    }
}
