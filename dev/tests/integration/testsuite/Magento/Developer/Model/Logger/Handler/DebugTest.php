<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Developer\Model\Logger\Handler;

use Magento\Config\Setup\ConfigOptionsList;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\State;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Logger\Monolog;
use Magento\Framework\Shell;
use Magento\Setup\Mvc\Bootstrap\InitParamListener;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DebugTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Monolog
     */
    private $logger;

    /**
     * @var WriteInterface
     */
    private $etcDirectory;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Shell
     */
    private $shell;

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var string
     */
    private $debugLogPath = '';

    /**
     * @var string
     */
    private static $backupFile = 'env.base.php';

    /**
     * @var string
     */
    private static $configFile = 'env.php';

    /**
     * @var Debug
     */
    private $debugHandler;

    /**
     * @inheritdoc
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Exception
     */
    public function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->shell = $this->objectManager->get(Shell::class);
        $this->logger = $this->objectManager->get(Monolog::class);
        $this->deploymentConfig = $this->objectManager->get(DeploymentConfig::class);

        /** @var Filesystem $filesystem */
        $filesystem = $this->objectManager->create(Filesystem::class);
        $this->etcDirectory = $filesystem->getDirectoryWrite(DirectoryList::CONFIG);
        $this->etcDirectory->copyFile(self::$configFile, self::$backupFile);
    }

    /**
     * @inheritdoc
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function tearDown()
    {
        $this->reinitDeploymentConfig();
        $this->etcDirectory->delete(self::$backupFile);
    }

    /**
     * @param bool $flag
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function enableDebugging(bool $flag)
    {
<<<<<<< HEAD
        $this->inputMock = $this->getMockBuilder(InputInterface::class)
            ->getMockForAbstractClass();
        $this->outputMock = $this->getMockBuilder(OutputInterface::class)
            ->getMockForAbstractClass();
        $this->inputMock->expects($this->exactly(4))
            ->method('getOption')
            ->withConsecutive(
                [ConfigSetCommand::OPTION_LOCK_ENV],
                [ConfigSetCommand::OPTION_LOCK_CONFIG],
                [ConfigSetCommand::OPTION_SCOPE],
                [ConfigSetCommand::OPTION_SCOPE_CODE]
            )
            ->willReturnOnConsecutiveCalls(
                true,
                false,
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                null
            );
        $this->inputMock->expects($this->exactly(2))
            ->method('getArgument')
            ->withConsecutive([ConfigSetCommand::ARG_PATH], [ConfigSetCommand::ARG_VALUE])
            ->willReturnOnConsecutiveCalls('dev/debug/debug_logging', 1);
        $this->outputMock->expects($this->once())
            ->method('writeln')
            ->with('<info>Value was saved in app/etc/env.php and locked.</info>');
        $this->assertFalse((bool)$this->configSetCommand->run($this->inputMock, $this->outputMock));
=======
        $this->shell->execute(
            PHP_BINARY . ' -f %s setup:config:set -n --%s=%s --%s=%s',
            [
                BP . '/bin/magento',
                ConfigOptionsList::INPUT_KEY_DEBUG_LOGGING,
                (int)$flag,
                InitParamListener::BOOTSTRAP_PARAM,
                urldecode(http_build_query(Bootstrap::getInstance()->getAppInitParams())),
            ]
        );
        $this->deploymentConfig->resetData();
        $this->assertSame((int)$flag, $this->deploymentConfig->get(ConfigOptionsList::CONFIG_PATH_DEBUG_LOGGING));
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testDebugInProductionMode()
    {
        $message = 'test message';
        $this->reinitDebugHandler(State::MODE_PRODUCTION);

        $this->removeDebugLog();
        $this->logger->debug($message);
        $this->assertFileNotExists($this->getDebuggerLogPath());
        $this->assertNull($this->deploymentConfig->get(ConfigOptionsList::CONFIG_PATH_DEBUG_LOGGING));

        $this->checkCommonFlow($message);
        $this->reinitDeploymentConfig();
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testDebugInDeveloperMode()
    {
        $message = 'test message';
        $this->reinitDebugHandler(State::MODE_DEVELOPER);

        $this->removeDebugLog();
        $this->logger->debug($message);
        $this->assertFileExists($this->getDebuggerLogPath());
        $this->assertContains($message, file_get_contents($this->getDebuggerLogPath()));
        $this->assertNull($this->deploymentConfig->get(ConfigOptionsList::CONFIG_PATH_DEBUG_LOGGING));

        $this->checkCommonFlow($message);
        $this->reinitDeploymentConfig();
    }

    /**
     * @return string
     */
    private function getDebuggerLogPath()
    {
        if (!$this->debugLogPath) {
            foreach ($this->logger->getHandlers() as $handler) {
                if ($handler instanceof Debug) {
                    $this->debugLogPath = $handler->getUrl();
                }
            }
        }

        return $this->debugLogPath;
    }

    /**
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function reinitDeploymentConfig()
    {
        $this->etcDirectory->delete(self::$configFile);
        $this->etcDirectory->copyFile(self::$backupFile, self::$configFile);
    }

    /**
     * @param string $instanceMode
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function reinitDebugHandler(string $instanceMode)
    {
        $this->debugHandler = $this->objectManager->create(
            Debug::class,
            [
                'filePath' => Bootstrap::getInstance()->getAppTempDir(),
                'state' => $this->objectManager->create(
                    State::class,
                    [
                        'mode' => $instanceMode,
                    ]
                ),
            ]
        );
        $this->logger->setHandlers(
            [
                $this->debugHandler,
            ]
        );
    }

    /**
     * @return void
     */
    private function detachLogger()
    {
        $this->debugHandler->close();
    }

    /**
     * @return void
     */
    private function removeDebugLog()
    {
        $this->detachLogger();
        if (file_exists($this->getDebuggerLogPath())) {
            unlink($this->getDebuggerLogPath());
        }
    }

    /**
     * @param string $message
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function checkCommonFlow(string $message)
    {
        $this->enableDebugging(true);
        $this->removeDebugLog();
        $this->logger->debug($message);
        $this->assertFileExists($this->getDebuggerLogPath());
        $this->assertContains($message, file_get_contents($this->getDebuggerLogPath()));

        $this->enableDebugging(false);
        $this->removeDebugLog();
        $this->logger->debug($message);
        $this->assertFileNotExists($this->getDebuggerLogPath());
    }
}
