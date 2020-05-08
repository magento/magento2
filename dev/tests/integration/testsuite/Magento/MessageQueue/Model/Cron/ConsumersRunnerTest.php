<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MessageQueue\Model\Cron;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Lock\Backend\Database;
use Magento\Framework\MessageQueue\Consumer\ConfigInterface as ConsumerConfigInterface;
use Magento\Framework\Lock\LockManagerInterface;
use Magento\Framework\App\DeploymentConfig\FileReader;
use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\ShellInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Config\ReinitableConfigInterface;

/**
 * Tests the different cases of consumers running by ConsumersRunner
 *
 * {@inheritdoc}
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConsumersRunnerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Consumer config provider
     *
     * @var ConsumerConfigInterface
     */
    private $consumerConfig;

    /**
     * @var LockManagerInterface
     */
    private $lockManager;

    /**
     * @var FileReader
     */
    private $reader;

    /**
     * @var ConsumersRunner
     */
    private $consumersRunner;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var ConfigFilePool
     */
    private $configFilePool;

    /**
     * @var ReinitableConfigInterface
     */
    private $appConfig;

    /**
     * @var ShellInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $shellMock;

    /**
     * @var array
     */
    private $config;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->shellMock = $this->getMockBuilder(ShellInterface::class)
            ->getMockForAbstractClass();
        $resourceConnection = $this->objectManager->create(ResourceConnection::class);
        $deploymentConfig = $this->objectManager->create(DeploymentConfig::class);
        // create object with new otherwise dummy locker is created because of di.xml preference for integration tests
        $this->lockManager = new Database($resourceConnection, $deploymentConfig);
        $this->consumerConfig = $this->objectManager->get(ConsumerConfigInterface::class);
        $this->reader = $this->objectManager->get(FileReader::class);
        $this->filesystem = $this->objectManager->get(Filesystem::class);
        $this->configFilePool = $this->objectManager->get(ConfigFilePool::class);
        $this->appConfig = $this->objectManager->get(ReinitableConfigInterface::class);
        $this->consumersRunner = $this->objectManager->create(
            ConsumersRunner::class,
            ['shellBackground' => $this->shellMock]
        );
        $this->config = $this->loadConfig();

        $this->shellMock->expects($this->any())
            ->method('execute')
            ->willReturnCallback(
                function ($command, $arguments) {
                    $command = vsprintf($command, $arguments);
                    $params = \Magento\TestFramework\Helper\Bootstrap::getInstance()->getAppInitParams();
                    $params['MAGE_DIRS']['base']['path'] = BP;
                    $params = 'INTEGRATION_TEST_PARAMS="' . urldecode(http_build_query($params)) . '"';
                    $command = str_replace('bin/magento', 'dev/tests/integration/bin/magento', $command);
                    $command = $params . ' ' . $command;

                    return exec("{$command} >/dev/null &"); //phpcs:ignore
                }
            );
    }

    /**
     * Tests running of specific consumer and his re-running when it is working
     *
     * @return void
     */
    public function testSpecificConsumerAndRerun()
    {
        $specificConsumer = 'exportProcessor';
        $config = $this->config;
        $config['cron_consumers_runner'] = ['consumers' => [$specificConsumer], 'max_messages' => 0];
        $this->writeConfig($config);
        $this->reRunConsumersAndCheckLocks($specificConsumer);
        $this->reRunConsumersAndCheckLocks($specificConsumer);
        $this->assertTrue($this->lockManager->isLocked(md5($specificConsumer))); //phpcs:ignore
    }

    /**
     * @param string $specificConsumer
     * @return void
     */
    private function reRunConsumersAndCheckLocks($specificConsumer)
    {
        $this->consumersRunner->run();

        sleep(20);

        foreach ($this->consumerConfig->getConsumers() as $consumer) {
            $consumerName = $consumer->getName();

            if ($consumerName === $specificConsumer) {
                $this->assertTrue($this->lockManager->isLocked(md5($consumerName))); //phpcs:ignore
            } else {
                $this->assertFalse($this->lockManager->isLocked(md5($consumerName))); //phpcs:ignore
            }
        }
    }

    /**
     * Tests disabling cron job which runs consumers
     *
     * @return void
     */
    public function testCronJobDisabled()
    {
        $config = $this->config;
        $config['cron_consumers_runner'] = ['cron_run' => false];

        $this->writeConfig($config);

        $this->consumersRunner->run();

        sleep(20);

        foreach ($this->consumerConfig->getConsumers() as $consumer) {
            $this->assertFalse($this->lockManager->isLocked(md5($consumer->getName()))); //phpcs:ignore
        }
    }

    /**
     * @return array
     */
    private function loadConfig()
    {
        return $this->reader->load(ConfigFilePool::APP_ENV);
    }

    /**
     * @param array $config
     * @return void
     */
    private function writeConfig(array $config)
    {
        /** @var Writer $writer */
        $writer = $this->objectManager->get(Writer::class);
        $writer->saveConfig([ConfigFilePool::APP_ENV => $config]);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        foreach ($this->consumerConfig->getConsumers() as $consumer) {
            foreach ($this->getConsumerProcessIds($consumer->getName()) as $consumerProcessId) {
                exec("kill {$consumerProcessId}"); //phpcs:ignore
            }
        }

        $this->filesystem->getDirectoryWrite(DirectoryList::CONFIG)->writeFile(
            $this->configFilePool->getPath(ConfigFilePool::APP_ENV),
            "<?php\n return array();\n"
        );
        $this->writeConfig($this->config);
        $this->appConfig->reinit();
    }

    /**
     * Get Consumer ProcessIds
     *
     * @param string $consumer
     * @return string[]
     */
    private function getConsumerProcessIds($consumer)
    {
        //phpcs:ignore
        exec("ps ax | grep -v grep | grep 'queue:consumers:start {$consumer}' | awk '{print $1}'", $output);
        return $output;
    }
}
