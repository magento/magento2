<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TestFramework\Annotation;

use PHPUnit\Framework\Exception;
use Symfony\Component\Console\Tester\CommandTester;
use Magento\TestFramework\App\Config;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Console\Command\PriceIndexerDimensionsModeSetCommand as SetModeCommand;
use Magento\Catalog\Model\Indexer\Product\Price\DimensionModeConfiguration;

/**
 * Implementation of the @magentoIndexerDimensionMode DocBlock annotation
 */
class IndexerDimensionMode
{
    private $db;
    private $isDimensionMode = false;

    private $setModeCommand;

    private $objectManager;

    public function __construct($application)
    {
        $this->db = $application->getDbInstance();
    }

    private function restoreDb()
    {
        $this->db = \Magento\TestFramework\Helper\Bootstrap::getInstance()->getBootstrap()
            ->getApplication()
            ->getDbInstance();

        $this->db->restoreFromDbDump();
    }

    private function initCommand()
    {
        if (!$this->setModeCommand) {
            $this->objectManager = Bootstrap::getObjectManager();
            $command = $this->objectManager->create(SetModeCommand::class);
            $this->setModeCommand = new CommandTester($command);
        }
    }

    /**
     * @param string $mode
     */
    private function setDimensionMode($mode = DimensionModeConfiguration::DIMENSION_WEBSITE_AND_CUSTOMER_GROUP)
    {
        $this->initCommand();
        $this->objectManager->get(Config::class)->clean();
        $this->setModeCommand->execute([SetModeCommand::INPUT_KEY_MODE => $mode]);
        $this->objectManager->get(Config::class)->clean();
    }

     /**
     * Handler for 'startTest' event
     *
     * @param \PHPUnit\Framework\TestCase $test
     * @return void
     */
    public function startTest(\PHPUnit\Framework\TestCase $test)
    {
        $source = $test->getAnnotations();

        if (isset($source['method']['magentoIndexerDimensionMode'])) {
            $annotations = $source['method']['magentoIndexerDimensionMode'];
        } elseif (isset($source['class']['magentoIndexerDimensionMode'])) {
            $annotations = $source['class']['magentoIndexerDimensionMode'];
        } else {
            return;
        }

        $dbIsolation = $source['method']['magentoDbIsolation'] ?? $source['class']['magentoDbIsolation'] ?? ['disabled'];
        if ($dbIsolation[0] != 'disabled') {
            $this->fail("Invalid @magentoDbIsolation declaration: $dbIsolation[0]", $test);
        }

        if ($annotations[0] == 'price') {
            $this->isDimensionMode = true;
            $this->setDimensionMode();
        }
    }

    /**
     * Handler for 'endTest' event
     *
     * @param \PHPUnit\Framework\TestCase $test
     * @return void
     */
    public function endTest(\PHPUnit\Framework\TestCase $test)
    {
        if ($this->isDimensionMode) {
            $this->restoreDb();
            $this->objectManager->get(Config::class)->clean();
            $this->isDimensionMode = false;
        }
    }

    /**
     * Fails the test with specified error message
     *
     * @param string $message
     * @param \PHPUnit\Framework\TestCase $test
     * @throws \Exception
     */
    private function fail($message, \PHPUnit\Framework\TestCase $test)
    {
        $test->fail("{$message} in the test '{$test->toString()}'");
    }
}
