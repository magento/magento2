<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Console\Command;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Console\Cli;
use Magento\Framework\Filesystem;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\App\State;
use Symfony\Component\Console\Tester\CommandTester;
use Magento\Deploy\Console\ConsoleLogger;
use Magento\Deploy\Console\ConsoleLoggerFactory;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\App\DeploymentConfig\FileReader;
use Magento\Framework\App\DeploymentConfig\Writer;

/**
 * Tests working status of deploy:mode:set command.
 *
 * {@inheritdoc}
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class SetModeCommandTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var CommandTester
     */
    private $commandTester;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var string
     */
    private $prevMode;

    /**
     * @var FileReader
     */
    private $reader;

    /**
     * @var Writer
     */
    private $writer;

    /**
     * @var array
     */
    private $config;

    /**
     * @var array
     */
    private $envConfig;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->reader = $this->objectManager->get(FileReader::class);
        $this->writer = $this->objectManager->get(Writer::class);
        $this->prevMode = $this->objectManager->get(State::class)->getMode();
        $this->filesystem = $this->objectManager->get(Filesystem::class);

        // Load the original config to restore it on teardown
        $this->config = $this->reader->load(ConfigFilePool::APP_CONFIG);
        $this->envConfig = $this->reader->load(ConfigFilePool::APP_ENV);
    }

    /**
     * @inheritdoc
     */
    public function tearDown()
    {
        // Restore the original config
        $this->writer->saveConfig([ConfigFilePool::APP_CONFIG => $this->config]);
        $this->writer->saveConfig([ConfigFilePool::APP_ENV => $this->envConfig]);

        $this->clearStaticFiles();
        // enable default mode
        $this->commandTester = new CommandTester($this->getStaticContentDeployCommand());
        $this->commandTester->execute(
            ['mode' => 'default']
        );
        $commandOutput = $this->commandTester->getDisplay();
        $this->assertEquals(Cli::RETURN_SUCCESS, $this->commandTester->getStatusCode());
        $this->assertContains('Enabled default mode', $commandOutput);
    }

    /**
     * Clear pub/static and var/view_preprocessed directories
     *
     * @return void
     */
    private function clearStaticFiles()
    {
        $this->filesystem->getDirectoryWrite(DirectoryList::PUB)->delete(DirectoryList::STATIC_VIEW);
        $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR)->delete(DirectoryList::TMP_MATERIALIZATION_DIR);
    }

    public function testSwitchMode()
    {
        if ($this->prevMode === 'production') {
            //in production mode, so we have to switch to dev, then to production
            $this->enableAndAssertDeveloperMode();
            $this->enableAndAssertProductionMode();
        } else {
            //already in non production mode
            $this->enableAndAssertProductionMode();
        }
    }

    /**
     * Enable production mode
     *
     * @return void
     */
    private function enableAndAssertProductionMode()
    {
        // Enable production mode
        $this->commandTester = new CommandTester($this->getStaticContentDeployCommand());
        $this->commandTester->execute(
            ['mode' => 'production']
        );
        $commandOutput = $this->commandTester->getDisplay();

        $this->assertEquals(Cli::RETURN_SUCCESS, $this->commandTester->getStatusCode(), $commandOutput);

        $this->assertContains('Deployment of static content complete', $commandOutput);
        $this->assertContains('Enabled production mode', $commandOutput);
    }

    /**
     * Enable developer mode
     *
     * @return void
     */
    private function enableAndAssertDeveloperMode()
    {
        $this->commandTester = new CommandTester($this->getStaticContentDeployCommand());
        $this->commandTester->execute(
            ['mode' => 'developer']
        );
        $commandOutput = $this->commandTester->getDisplay();

        $this->assertEquals(Cli::RETURN_SUCCESS, $this->commandTester->getStatusCode());
        $this->assertContains('Enabled developer mode', $commandOutput);
    }

    /**
     * Create SetModeCommand instance with mocked loggers
     *
     * @return SetModeCommand
     */
    private function getStaticContentDeployCommand()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $consoleLoggerFactoryMock = $this->getMockBuilder(ConsoleLoggerFactory::class)
            ->setMethods(['getLogger'])
            ->disableOriginalConstructor()
            ->getMock();
        $consoleLoggerFactoryMock
            ->method('getLogger')
            ->will($this->returnCallback(
                function ($output) use ($objectManager) {
                    return $objectManager->create(ConsoleLogger::class, ['output' => $output]);
                }
            ));
        $objectManagerProviderMock = $this->getMockBuilder(ObjectManagerProvider::class)
            ->setMethods(['get'])
            ->disableOriginalConstructor()
            ->getMock();
        $objectManagerProviderMock
            ->method('get')
            ->willReturn(\Magento\TestFramework\Helper\Bootstrap::getObjectManager());
        $deployStaticContentCommand = $objectManager->create(
            SetModeCommand::class,
            [
                'consoleLoggerFactory' => $consoleLoggerFactoryMock,
                'objectManagerProvider' => $objectManagerProviderMock
            ]
        );

        return $deployStaticContentCommand;
    }
}
