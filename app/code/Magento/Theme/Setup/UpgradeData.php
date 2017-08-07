<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Setup;

use Magento\Framework\DB\DataConverter\SerializedToJson;
use Magento\Framework\DB\FieldDataConverterFactory;
use Magento\Framework\DB\Select\QueryModifierFactory;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Theme\Model\Data\Design\Config;

/**
 * @codeCoverageIgnore
 * @since 2.1.0
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var FieldDataConverterFactory
     * @since 2.2.0
     */
    private $fieldDataConverterFactory;

    /**
     * @var QueryModifierFactory
     * @since 2.2.0
     */
    private $queryModifierFactory;

    /**
     * @var IndexerRegistry
     * @since 2.1.0
     */
    protected $indexerRegistry;

    /**
     * UpgradeData constructor
     *
     * @param IndexerRegistry $indexerRegistry
     * @param FieldDataConverterFactory $fieldDataConverterFactory
     * @param QueryModifierFactory $queryModifierFactory
     * @since 2.1.0
     */
    public function __construct(
        IndexerRegistry $indexerRegistry,
        FieldDataConverterFactory $fieldDataConverterFactory,
        QueryModifierFactory $queryModifierFactory
    ) {
        $this->fieldDataConverterFactory = $fieldDataConverterFactory;
        $this->queryModifierFactory = $queryModifierFactory;
        $this->indexerRegistry = $indexerRegistry;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $indexer = $this->indexerRegistry->get(Config::DESIGN_CONFIG_GRID_INDEXER_ID);
        $indexer->reindexAll();
        if (version_compare($context->getVersion(), '2.0.2', '<')) {
            $this->upgradeToVersionTwoZeroTwo($setup);
        }
        $setup->endSetup();
    }

    /**
     * Upgrade to version 2.0.2, convert data for `value` field in `core_config_data table`
     * from php-serialized to JSON format
     *
     * @param ModuleDataSetupInterface $setup
     * @return void
     * @since 2.2.0
     */
    private function upgradeToVersionTwoZeroTwo(ModuleDataSetupInterface $setup)
    {
        $fieldDataConverter = $this->fieldDataConverterFactory->create(SerializedToJson::class);
        $queryModifier = $this->queryModifierFactory->create(
            'in',
            [
                'values' => [
                    'path' => [
                        'design/theme/ua_regexp',
                    ]
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
}
