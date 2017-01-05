<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\UseCase;

use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\MessageQueue\PublisherInterface;

/**
 * Base test case for message queue tests.
 */
abstract class QueueTestCaseAbstract extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string[]
     */
    protected $consumers = [];

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var PublisherInterface
     */
    protected $publisher;

    /**
     * @var string
     */
    protected $logFilePath;

    /**
     * @var int|null
     */
    protected $maxMessages = null;

    protected function setUp()
    {
        $this->markTestSkipped(
            "These tests are unstable on php56. Skipped until fixed in reliable manner MAGETWO-60476"
        );
        $this->objectManager = Bootstrap::getObjectManager();
        /** @var \Magento\Framework\OsInfo $osInfo */
        $osInfo = $this->objectManager->get(\Magento\Framework\OsInfo::class);
        if ($osInfo->isWindows()) {
            $this->markTestSkipped("This test relies on *nix shell and should be skipped in Windows environment.");
        }
        foreach ($this->consumers as $consumer) {
            foreach ($this->getConsumerProcessIds($consumer) as $consumerProcessId) {
                exec("kill {$consumerProcessId}");
            }
        }
        foreach ($this->consumers as $consumer) {
            if (!$this->getConsumerProcessIds($consumer)) {
                exec("{$this->getConsumerStartCommand($consumer, true)} > /dev/null &");
            }
        }

        $this->logFilePath = TESTS_TEMP_DIR . "/MessageQueueTestLog.txt";
        if (file_exists($this->logFilePath)) {
            // try to remove before failing the test
            unlink($this->logFilePath);
            if (file_exists($this->logFilePath)) {
                $this->fail(
                    "Precondition failed: test log ({$this->logFilePath}) cannot be deleted before test execution."
                );
            }
        }

        $this->publisher = $this->objectManager->get(PublisherInterface::class);
    }

    protected function tearDown()
    {
        foreach ($this->consumers as $consumer) {
            foreach ($this->getConsumerProcessIds($consumer) as $consumerProcessId) {
                exec("kill {$consumerProcessId}");
            }
        }
    }

    /**
     * @param string $consumer
     * @return string[]
     */
    protected function getConsumerProcessIds($consumer)
    {
        exec("ps ax | grep -v grep | grep '{$this->getConsumerStartCommand($consumer)}' | awk '{print $1}'", $output);
        return $output;
    }

    /**
     * Get CLI command for starting specified consumer.
     *
     * @param string $consumer
     * @param bool $withEnvVariables
     * @return string
     */
    protected function getConsumerStartCommand($consumer, $withEnvVariables = false)
    {
        $binDirectory = realpath(TESTS_TEMP_DIR . '/../bin/');
        $magentoCli = $binDirectory . '/magento';
        $consumerStartCommand = "php {$magentoCli} queue:consumers:start -vvv " . $consumer;
        if ($this->maxMessages) {
            $consumerStartCommand .= " --max-messages={$this->maxMessages}";
        }
        if ($withEnvVariables) {
            $params = \Magento\TestFramework\Helper\Bootstrap::getInstance()->getAppInitParams();
            $params['MAGE_DIRS']['base']['path'] = BP;
            $params = 'INTEGRATION_TEST_PARAMS="' . urldecode(http_build_query($params)) . '"';
            $consumerStartCommand = $params . ' ' . $consumerStartCommand;
        }
        return $consumerStartCommand;
    }

    /**
     * Wait for asynchronous handlers to log data to file.
     *
     * @param int $expectedLinesCount
     * @param string $logFilePath
     */
    protected function waitForAsynchronousResult($expectedLinesCount, $logFilePath)
    {
        $i = 0;
        do {
            sleep(1);
            $actualCount = file_exists($logFilePath) ? count(file($logFilePath)) : 0;
        } while (($expectedLinesCount !== $actualCount) && ($i++ < 180));

        if (!file_exists($logFilePath)) {
            $this->fail("No asynchronous messages were processed.");
        }
    }

    /**
     * Workaround for https://bugs.php.net/bug.php?id=72286
     */
    public static function tearDownAfterClass()
    {
        if (version_compare(phpversion(), '7') == -1) {
            $closeConnection = new \ReflectionMethod(\Magento\Amqp\Model\Config::class, 'closeConnection');
            $closeConnection->setAccessible(true);

            $config = Bootstrap::getObjectManager()->get(\Magento\Amqp\Model\Config::class);
            $closeConnection->invoke($config);
        }
    }
}
