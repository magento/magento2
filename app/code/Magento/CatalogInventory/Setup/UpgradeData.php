<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Setup;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Framework\DB\DataConverter\SerializedToJson;
use Magento\Framework\DB\FieldDataConverterFactory;
use Magento\Framework\DB\Select\QueryModifierFactory;
use Magento\Framework\Indexer\AbstractProcessor;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Upgrade Data script
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var StockConfigurationInterface
     */
    private $configuration;

    /**
     * @var AbstractProcessor
     */
    private $indexerProcessor;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var FieldDataConverterFactory
     */
    private $fieldDataConverterFactory;

    /**
     * @var QueryModifierFactory
     */
    private $queryModifierFactory;

    /**
     * @param StockConfigurationInterface $configuration
     * @param StoreManagerInterface $storeManager
     * @param AbstractProcessor $indexerProcessor
     * @param FieldDataConverterFactory $fieldDataConverterFactory
     * @param QueryModifierFactory $queryModifierFactory
     */
    public function __construct(
        StockConfigurationInterface $configuration,
        StoreManagerInterface $storeManager,
        AbstractProcessor $indexerProcessor,
        FieldDataConverterFactory $fieldDataConverterFactory,
        QueryModifierFactory $queryModifierFactory
    ) {
        $this->configuration = $configuration;
        $this->storeManager = $storeManager;
        $this->indexerProcessor = $indexerProcessor;
        $this->fieldDataConverterFactory = $fieldDataConverterFactory;
        $this->queryModifierFactory = $queryModifierFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '2.2.0') < 0) {
            $this->upgradeCatalogInventoryStockItem($setup);
        }

        if (version_compare($context->getVersion(), '2.2.1', '<')) {
            $this->convertSerializedDataToJson($setup);
        }
        $setup->endSetup();
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @return void
     */
    private function upgradeCatalogInventoryStockItem($setup)
    {
        $setup->getConnection()->update(
            $setup->getTable('cataloginventory_stock_item'),
            ['website_id' => $this->configuration->getDefaultScopeId()],
            ['website_id = ?' => $this->storeManager->getWebsite()->getId()]
        );
        $this->indexerProcessor->getIndexer()->invalidate();
    }

    /**
     * Upgrade data to version 2.2.1, converts row data in the core_config_data table that uses the
     * path cataloginventory/item_options/min_sale_qty from serialized to JSON. Stored value may not be
     * serialized, so validate data format before executing update.
     *
     * @param ModuleDataSetupInterface $setup
     * @return void
     */
    private function convertSerializedDataToJson(ModuleDataSetupInterface $setup)
    {
        $select = $setup->getConnection()
            ->select()
            ->from(
                $setup->getTable('core_config_data'),
                ['config_id', 'value']
            )
            ->where('path = ?', 'cataloginventory/item_options/min_sale_qty');

        $rows = $setup->getConnection()->fetchAssoc($select);
        $serializedRows = array_filter($rows, function ($row) {
            return $this->isSerialized($row['value']);
        });

        $fieldDataConverter = $this->fieldDataConverterFactory->create(SerializedToJson::class);
        $queryModifier = $this->queryModifierFactory->create(
            'in',
            [
                'values' => [
                    'config_id' => array_keys($serializedRows)
                ]
            ]
        );

        $fieldDataConverter->convert(
            $setup->getConnection(),
            $setup->getTable('core_config_data'),
            'config_id',
            'value',
            $queryModifier
        );
    }

    /**
     * Check if value is a serialized string
     *
     * @param string $value
     * @return boolean
     */
    private function isSerialized($value)
    {
        return (boolean) preg_match('/^((s|i|d|b|a|O|C):|N;)/', $value);
    }
}
