<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Annotation;

use Magento\Catalog\Model\Indexer\Product\Price\DimensionModeConfiguration;
use Magento\Catalog\Model\Indexer\Product\Price\ModeSwitcher;
use Magento\Catalog\Model\Indexer\Product\Price\ModeSwitcherConfiguration;
use Magento\Catalog\Model\Indexer\Product\Price\Processor;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\App\Config;
use Magento\TestFramework\Fixture\ParserInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Implementation of the @magentoIndexerDimensionMode DocBlock annotation
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IndexerDimensionMode
{
    /** @var TypeListInterface */
    private $cacheTypeList;

    /** @var ScopeConfigInterface */
    private $configReader;

    /** @var ModeSwitcher */
    private $modeSwitcher;

    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var \Magento\TestFramework\Db\Mysql */
    private $db;

    /** @var bool */
    private $isDimensionMode = false;

    /**
     * Restore db
     */
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
     * Tries to set a Dimension mode if it wasn't set.
     *
     * @param string $mode
     * @param TestCase $test
     *
     * @return void
     * @throws \Exception
     */
    private function setDimensionMode(string $mode, TestCase $test)
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->modeSwitcher = $this->objectManager->get(ModeSwitcher::class);
        $this->configReader = $this->objectManager->get(ScopeConfigInterface::class);
        $this->cacheTypeList = $this->objectManager->get(TypeListInterface::class);

        $this->configReader->clean();
        $previousMode = $this->configReader->getValue(ModeSwitcherConfiguration::XML_PATH_PRICE_DIMENSIONS_MODE) ?:
            DimensionModeConfiguration::DIMENSION_NONE;

        if ($previousMode !== $mode) {
            //Create new tables and move data
            $this->modeSwitcher->switchMode($mode, $previousMode);
            $this->objectManager->get(Config::class)->clean();
        } else {
            ExceptionHandler::handle(
                'Dimensions mode for indexer has not been changed',
                test: $test
            );
        }
    }

    /**
     * Handler for 'startTest' event
     *
     * @param TestCase $test
     * @return void
     * @throws \Exception
     */
    public function startTest(TestCase $test)
    {
        $values = [];
        try {
            $values = $this->parse($test);
        } catch (\Throwable $exception) {
            ExceptionHandler::handle(
                'Unable to parse fixtures',
                $exception,
                $test
            );
        }

        if ($values) {
            $dbIsolation = Bootstrap::getObjectManager()->get(DbIsolationState::class)->isEnabled($test);
            if ($dbIsolation) {
                ExceptionHandler::handle(
                    '@magentoDbIsolation must be disabled when using @magentoIndexerDimensionMode',
                    test: $test
                );
            }

            if ($values[0]['indexer'] === Processor::INDEXER_ID) {
                $this->isDimensionMode = true;
                $this->setDimensionMode($values[0]['dimension'], $test);
            }
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
     * Returns IndexerDimensionMode fixtures configuration
     *
     * @param TestCase $test
     * @return array
     * @throws LocalizedException
     */
    private function parse(TestCase $test): array
    {
        $objectManager = Bootstrap::getObjectManager();
        $parsers = $objectManager
            ->create(
                \Magento\TestFramework\Annotation\Parser\Composite::class,
                [
                    'parsers' => [
                        $objectManager->get(\Magento\TestFramework\Annotation\Parser\IndexerDimensionMode::class),
                        $objectManager->get(\Magento\TestFramework\Fixture\Parser\IndexerDimensionMode::class)
                    ]
                ]
            );
        return $parsers->parse($test, ParserInterface::SCOPE_METHOD)
            ?: $parsers->parse($test, ParserInterface::SCOPE_CLASS);
    }
}
