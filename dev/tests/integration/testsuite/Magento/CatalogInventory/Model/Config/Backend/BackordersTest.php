<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Model\Config\Backend;

use Magento\CatalogInventory\Model\Configuration;
use Magento\CatalogInventory\Model\Indexer\Stock\Processor;
use Magento\CatalogInventory\Model\Stock;
use Magento\Config\Model\Config\BackendFactory;
use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Framework\Indexer\StateInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Checks that the backorders config backend model is working correctly
 */
class BackordersTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var Backorders */
    private $backorders;

    /** @var BackendFactory */
    private $backendFactory;

    /** @var MutableScopeConfigInterface */
    private $mutableConfig;

    /** @var Processor */
    private $stockIndexerProcessor;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->backendFactory = $this->objectManager->create(BackendFactory::class);
        $this->backorders = $this->backendFactory->create(Backorders::class, [
            'data' => [
                'path' => Configuration::XML_PATH_BACKORDERS,
            ]
        ]);
        $this->mutableConfig = $this->objectManager->get(MutableScopeConfigInterface::class);
        $this->stockIndexerProcessor = $this->objectManager->get(Processor::class);
    }

    /**
     * @dataProvider afterSaveDataProvider
     * @param int $value
     * @param int $currentValue
     * @param string $expectedIndexerStatus
     * @magentoDbIsolation disabled
     * @return void
     */
    public function testAfterSave(int $value, int $currentValue, string $expectedIndexerStatus): void
    {
        $this->stockIndexerProcessor->reindexAll();
        $this->mutableConfig->setValue(Configuration::XML_PATH_BACKORDERS, $currentValue);
        $this->backorders->setValue((string)$value);
        $this->backorders->afterSave();

        $this->assertEquals($expectedIndexerStatus, $this->stockIndexerProcessor->getIndexer()->getStatus());
    }

    /**
     * Data provider for testAfterSave
     *
     * @return array
     */
    public static function afterSaveDataProvider(): array
    {
        return [
            'set_backorders' => [
                'value' => Stock::BACKORDERS_YES_NONOTIFY,
                'currentValue' => Stock::BACKORDERS_NO,
                'expectedIndexerStatus' => StateInterface::STATUS_INVALID,
            ],
            'unset_backorders' => [
                'value' => Stock::BACKORDERS_NO,
                'currentValue' => Stock::BACKORDERS_YES_NONOTIFY,
                'expectedIndexerStatus' => StateInterface::STATUS_INVALID,
            ],
            'same_backorders' => [
                'value' => Stock::BACKORDERS_YES_NONOTIFY,
                'currentValue' => Stock::BACKORDERS_YES_NONOTIFY,
                'expectedIndexerStatus' => StateInterface::STATUS_VALID,
            ],
        ];
    }
}
