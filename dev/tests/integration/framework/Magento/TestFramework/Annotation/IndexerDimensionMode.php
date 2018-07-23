<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TestFramework\Annotation;

use Magento\Catalog\Model\Indexer\Product\Price\ModeSwitcher;
use Magento\Catalog\Model\Indexer\Product\Price\Processor;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\App\Config;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Model\Indexer\Product\Price\DimensionModeConfiguration;
use PHPUnit\Framework\TestCase;

/**
 * Implementation of the @magentoIndexerDimensionMode DocBlock annotation
 */
class IndexerDimensionMode
{
    /** @var TypeListInterface */
    private $cacheTypeList;

    /** @var ScopeConfigInterface */
    private $configReader;

    /** @var ModeSwitcher */
    private $modeSwitcher;

    /** @var ConfigInterface */
    private $configWriter;

    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var \Magento\TestFramework\Db\Mysql */
    private $db;

    /** @var bool */
    private $isDimensionMode = false;

    private function restoreDb()
    {
        $this->db = Bootstrap::getInstance()->getBootstrap()->getApplication()->getDbInstance();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->db->restoreFromDbDump();
        $this->cacheTypeList = $this->objectManager->get(TypeListInterface::class);
        $this->cacheTypeList->cleanType('config');
        $this->objectManager->get(Config::class)->clean();
    }

    /**
     * @param string $mode
     */
    private function setDimensionMode($mode, $test)
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->modeSwitcher = $this->objectManager->get(ModeSwitcher::class);
        $this->configWriter = $this->objectManager->get(ConfigInterface::class);
        $this->configReader = $this->objectManager->get(ScopeConfigInterface::class);
        $this->cacheTypeList = $this->objectManager->get(TypeListInterface::class);

        $this->configReader->clean();
        $previousMode = $this->configReader->getValue(ModeSwitcher::XML_PATH_PRICE_DIMENSIONS_MODE) ?:
            DimensionModeConfiguration::DIMENSION_NONE;

        if ($previousMode !== $mode) {
            //Create new tables and move data
            $this->modeSwitcher->createTables($mode);
            $this->modeSwitcher->moveData($mode, $previousMode);

            //Change config options
            $this->configWriter->saveConfig(ModeSwitcher::XML_PATH_PRICE_DIMENSIONS_MODE, $mode);
            $this->cacheTypeList->cleanType('config');
            $this->objectManager->get(Config::class)->clean();

            //Delete old tables
            $this->modeSwitcher->dropTables($previousMode);
        } else {
            $this->fail('Dimensions mode for indexer has not been changed', $test);
        }
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

        $dbIsolation = $source['method']['magentoDbIsolation']
            ?? $source['class']['magentoDbIsolation']
            ?? ['disabled'];

        if ($dbIsolation[0] != 'disabled') {
            $this->fail("Invalid @magentoDbIsolation declaration: $dbIsolation[0]", $test);
        }

        if ($annotations[0] == Processor::INDEXER_ID) {
            $this->isDimensionMode = true;
            $this->setDimensionMode(DimensionModeConfiguration::DIMENSION_WEBSITE_AND_CUSTOMER_GROUP, $test);
        }
    }

    /**
     * Handler for 'endTest' event
     *
     * @return void
     */
    public function endTest()
    {
        if ($this->isDimensionMode) {
            $this->restoreDb();
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
