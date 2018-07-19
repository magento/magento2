<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TestFramework\Annotation;

use Magento\Catalog\Model\Indexer\Product\Price\ModeSwitcher;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\TestFramework\Application;
use Magento\TestFramework\App\Config;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Model\Indexer\Product\Price\DimensionModeConfiguration;
use PHPUnit\Framework\TestCase;

/**
 * Implementation of the @magentoIndexerDimensionMode DocBlock annotation
 */
class IndexerDimensionMode
{
    private $modeSwithcer;

    private $configWriter;

    private $db;

    private $isDimensionMode = false;

    private $objectManager;

    public function __construct(Application $application)
    {
        $this->db = $application->getDbInstance();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->modeSwithcer = $this->objectManager->get(ModeSwitcher::class);
        $this->configWriter = $this->objectManager->get(ConfigInterface::class);
    }

    private function restoreDb()
    {
        $this->db->restoreFromDbDump();
    }

    /**
     * @param string $mode
     */
    private function setDimensionMode($mode = DimensionModeConfiguration::DIMENSION_WEBSITE_AND_CUSTOMER_GROUP)
    {
        $this->modeSwithcer->createTables($mode);
        $this->modeSwithcer->moveData($mode, DimensionModeConfiguration::DIMENSION_NONE);
        $this->configWriter->saveConfig(ModeSwitcher::XML_PATH_PRICE_DIMENSIONS_MODE, $mode);
        $this->objectManager->get(Config::class)->clean();
    }

     /**
     * Handler for 'startTest' event
     *
     * @param TestCase $test
     * @return void
     */
    public function startTest(TestCase $test)
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
     * @param TestCase $test
     * @return void
     */
    public function endTest(TestCase $test)
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
     * @param TestCase $test
     * @throws \Exception
     */
    private function fail($message, TestCase $test)
    {
        $test->fail("{$message} in the test '{$test->toString()}'");
    }
}
