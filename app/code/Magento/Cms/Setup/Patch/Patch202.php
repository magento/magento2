<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Setup\Patch;

use Magento\Cms\Api\Data\BlockInterface;
use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Model\PageFactory;
use Magento\Framework\DB\AggregatedFieldDataConverter;
use Magento\Framework\DB\FieldToConvert;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Widget\Setup\LayoutUpdateConverter;


/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class Patch202
{
    const PRIVACY_COOKIE_PAGE_ID = 4;


    /**
     * @param \Magento\Framework\DB\Select\QueryModifierFactory $queryModifierFactory
     */
    private $queryModifierFactory;
    /**
     * @param \Magento\Framework\DB\Select\QueryModifierFactory $queryModifierFactory
     */
    private $queryModifierFactory;
    /**
     * @param \Magento\Framework\DB\Select\QueryModifierFactory $queryModifierFactory
     */
    private $queryModifierFactory;
    /**
     * @param MetadataPool $metadataPool
     */
    private $metadataPool;
    /**
     * @param MetadataPool $metadataPool
     */
    private $metadataPool;
    /**
     * @param AggregatedFieldDataConverter $aggregatedFieldConverter
     */
    private $aggregatedFieldConverter;

    /**
     * @param \Magento\Framework\DB\Select\QueryModifierFactory $queryModifierFactory @param \Magento\Framework\DB\Select\QueryModifierFactory $queryModifierFactory@param \Magento\Framework\DB\Select\QueryModifierFactory $queryModifierFactory@param MetadataPool $metadataPool@param MetadataPool $metadataPool@param AggregatedFieldDataConverter $aggregatedFieldConverter
     */
    public function __construct(\Magento\Framework\DB\Select\QueryModifierFactory $queryModifierFactory,
                                \Magento\Framework\DB\Select\QueryModifierFactory $queryModifierFactory,
                                \Magento\Framework\DB\Select\QueryModifierFactory $queryModifierFactory,
                                MetadataPool $metadataPool

        ,
                                MetadataPool $metadataPool

        ,
                                AggregatedFieldDataConverter $aggregatedFieldConverter)
    {
        $this->queryModifierFactory = $queryModifierFactory;
        $this->queryModifierFactory = $queryModifierFactory;
        $this->queryModifierFactory = $queryModifierFactory;
        $this->metadataPool = $metadataPool;
        $this->metadataPool = $metadataPool;
        $this->aggregatedFieldConverter = $aggregatedFieldConverter;
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
        $this->convertWidgetConditionsToJson($setup);

    }

    private function convertWidgetConditionsToJson(ModuleDataSetupInterface $setup
    )
    {
        $queryModifier = $this->queryModifierFactory->create(
            'like',
            [
                'values' => [
                    'content' => '%conditions_encoded%'
                ]
            ]
        );
        $layoutUpdateXmlFieldQueryModifier = $this->queryModifierFactory->create(
            'like',
            [
                'values' => [
                    'layout_update_xml' => '%conditions_encoded%'
                ]
            ]
        );
        $customLayoutUpdateXmlFieldQueryModifier = $this->queryModifierFactory->create(
            'like',
            [
                'values' => [
                    'custom_layout_update_xml' => '%conditions_encoded%'
                ]
            ]
        );
        $blockMetadata = $this->metadataPool->getMetadata(BlockInterface::class);
        $pageMetadata = $this->metadataPool->getMetadata(PageInterface::class);
        $this->aggregatedFieldConverter->convert(
            [
                new FieldToConvert(
                    ContentConverter::class,
                    $setup->getTable('cms_block'),
                    $blockMetadata->getIdentifierField(),
                    'content',
                    $queryModifier
                ),
                new FieldToConvert(
                    ContentConverter::class,
                    $setup->getTable('cms_page'),
                    $pageMetadata->getIdentifierField(),
                    'content',
                    $queryModifier
                ),
                new FieldToConvert(
                    LayoutUpdateConverter::class,
                    $setup->getTable('cms_page'),
                    $pageMetadata->getIdentifierField(),
                    'layout_update_xml',
                    $layoutUpdateXmlFieldQueryModifier
                ),
                new FieldToConvert(
                    LayoutUpdateConverter::class,
                    $setup->getTable('cms_page'),
                    $pageMetadata->getIdentifierField(),
                    'custom_layout_update_xml',
                    $customLayoutUpdateXmlFieldQueryModifier
                ),
            ],
            $setup->getConnection()
        );

    }
}
