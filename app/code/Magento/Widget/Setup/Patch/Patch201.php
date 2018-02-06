<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Setup\Patch;

use Magento\Framework\DB\AggregatedFieldDataConverter;
use Magento\Framework\DB\DataConverter\SerializedToJson;
use Magento\Framework\DB\FieldToConvert;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;


/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class Patch201 implements \Magento\Setup\Model\Patch\DataPatchInterface
{


    /**
     * @param \Magento\Framework\DB\Select\QueryModifierFactory $queryModifierFactory
     */
    private $queryModifierFactory;
    /**
     * @param AggregatedFieldDataConverter $aggregatedFieldConverter
     */
    private $aggregatedFieldConverter;

    /**
     * @param \Magento\Framework\DB\Select\QueryModifierFactory $queryModifierFactory @param AggregatedFieldDataConverter $aggregatedFieldConverter
     */
    public function __construct(\Magento\Framework\DB\Select\QueryModifierFactory $queryModifierFactory

        ,
                                AggregatedFieldDataConverter $aggregatedFieldConverter)
    {
        $this->queryModifierFactory = $queryModifierFactory;
        $this->aggregatedFieldConverter = $aggregatedFieldConverter;
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
        $this->upgradeVersionTwoZeroOne($setup);

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


    private function upgradeVersionTwoZeroOne(ModuleDataSetupInterface $setup
    )
    {
        $layoutUpdateQueryModifier = $this->queryModifierFactory->create(
            'like',
            [
                'values' => [
                    'xml' => '%conditions_encoded%'
                ]
            ]
        );
        $this->aggregatedFieldConverter->convert(
            [
                new FieldToConvert(
                    SerializedToJson::class,
                    $setup->getTable('widget_instance'),
                    'instance_id',
                    'widget_parameters'
                ),
                new FieldToConvert(
                    LayoutUpdateConverter::class,
                    $setup->getTable('layout_update'),
                    'layout_update_id',
                    'xml',
                    $layoutUpdateQueryModifier
                ),
            ],
            $setup->getConnection()
        );

    }
}
