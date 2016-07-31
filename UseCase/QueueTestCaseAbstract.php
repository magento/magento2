<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->publisher = $this->objectManager->get(PublisherInterface::class);
        /** @var \Magento\Framework\OsInfo $osInfo */
        $osInfo = $this->objectManager->get(\Magento\Framework\OsInfo::class);
        if ($osInfo->isWindows()) {
            $this->markTestSkipped("This test relies on *nix shell and should be skipped in Windows environment.");
        }
        parent::setUp();
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
    }

    protected function tearDown()
    {
        parent::tearDown();
        foreach ($this->consumers as $consumer) {
            foreach ($this->getConsumerProcessIds($consumer) as $consumerProcessId) {
                exec("kill {$consumerProcessId}");
            }
        }
        if (file_exists($this->logFilePath)) {
            unlink($this->logFilePath);
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
        } while (($expectedLinesCount !== $actualCount) && ($i++ < 30));
    }
}
