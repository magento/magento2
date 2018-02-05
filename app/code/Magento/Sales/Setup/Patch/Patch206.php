<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Setup\Patch;

use Magento\Eav\Model\Config;
use Magento\Framework\DB\AggregatedFieldDataConverter;
use Magento\Framework\DB\DataConverter\SerializedToJson;
use Magento\Framework\DB\FieldToConvert;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Sales\Model\OrderFactory;


/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class Patch206
{


    /**
     * @param AggregatedFieldDataConverter $aggregatedFieldConverter
     */
    private $aggregatedFieldConverter;

    /**
     * @param AggregatedFieldDataConverter $aggregatedFieldConverter @param Config $eavConfig
     */
    public function __construct(AggregatedFieldDataConverter $aggregatedFieldConverter,
                                Config $eavConfig)
    {
        $this->aggregatedFieldConverter = $aggregatedFieldConverter;
        $this->eavConfig = $eavConfig;
    }

    /**
     * Do Upgrade
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function up(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $salesSetup = $this->salesSetupFactory->create(['setup' => $setup]);
        $this->convertSerializedDataToJson($context->getVersion(), $salesSetup);
        $this->eavConfig->clear();

    }

    private function convertSerializedDataToJson($setupVersion, SalesSetup $salesSetup
    )
    {
        $fieldsToUpdate = [
            new FieldToConvert(
                SerializedToJson::class,
                $salesSetup->getTable('sales_invoice_item'),
                'entity_id',
                'tax_ratio'
            ),
            new FieldToConvert(
                SerializedToJson::class,
                $salesSetup->getTable('sales_creditmemo_item'),
                'entity_id',
                'tax_ratio'
            ),
        ];
        Array        $this->aggregatedFieldConverter->convert($fieldsToUpdate, $salesSetup->getConnection());

    }
}
