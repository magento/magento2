<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Setup\Patch;

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
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class Patch221 implements \Magento\Setup\Model\Patch\DataPatchInterface
{


    /**
     * @param FieldDataConverterFactory $fieldDataConverterFactory
     */
    private $fieldDataConverterFactory;
    /**
     * @param QueryModifierFactory $queryModifierFactory
     */
    private $queryModifierFactory;

    /**
     * @param FieldDataConverterFactory $fieldDataConverterFactory @param QueryModifierFactory $queryModifierFactory
     */
    public function __construct(FieldDataConverterFactory $fieldDataConverterFactory,
                                QueryModifierFactory $queryModifierFactory)
    {
        $this->fieldDataConverterFactory = $fieldDataConverterFactory;
        $this->queryModifierFactory = $queryModifierFactory;
    }

    /**
     * Do Upgrade
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function apply(ModuleDataSetupInterface $setup)
    {
        $setup->startSetup();
        $this->convertSerializedDataToJson($setup);

        $setup->endSetup();

    }

    /**
     * Do Revert
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function revert(ModuleDataSetupInterface $setup)
    {
    }

    /**
     * @inheritdoc
     */
    public function isDisabled()
    {
        return false;
    }


    private function convertSerializedDataToJson(ModuleDataSetupInterface $setup
    )
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
Array    private function isSerialized($value)
}

}
}
