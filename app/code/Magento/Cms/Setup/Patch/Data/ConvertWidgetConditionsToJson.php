<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Setup\Patch\Data;

use Magento\Cms\Setup\ContentConverter;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select\QueryModifierFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\DB\AggregatedFieldDataConverter;
use Magento\Framework\DB\FieldToConvert;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Widget\Setup\LayoutUpdateConverter;
use Magento\Cms\Api\Data\BlockInterface;
use Magento\Cms\Api\Data\PageInterface;

/**
 * Class ConvertWidgetConditionsToJson
 * @package Magento\Cms\Setup\Patch
 */
class ConvertWidgetConditionsToJson implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var QueryModifierFactory
     */
    private $queryModifierFactory;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var AggregatedFieldDataConverter
     */
    private $aggregatedFieldDataConverter;

    /**
     * ConvertWidgetConditionsToJson constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param QueryModifierFactory $queryModifierFactory
     * @param MetadataPool $metadataPool
     * @param AggregatedFieldDataConverter $aggregatedFieldDataConverter
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        QueryModifierFactory $queryModifierFactory,
        MetadataPool $metadataPool,
        AggregatedFieldDataConverter $aggregatedFieldDataConverter
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->queryModifierFactory = $queryModifierFactory;
        $this->metadataPool = $metadataPool;
        $this->aggregatedFieldDataConverter = $aggregatedFieldDataConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
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
        $this->aggregatedFieldDataConverter->convert(
            [
                new FieldToConvert(
                    ContentConverter::class,
                    $this->moduleDataSetup->getTable('cms_block'),
                    $blockMetadata->getIdentifierField(),
                    'content',
                    $queryModifier
                ),
                new FieldToConvert(
                    ContentConverter::class,
                    $this->moduleDataSetup->getTable('cms_page'),
                    $pageMetadata->getIdentifierField(),
                    'content',
                    $queryModifier
                ),
                new FieldToConvert(
                    LayoutUpdateConverter::class,
                    $this->moduleDataSetup->getTable('cms_page'),
                    $pageMetadata->getIdentifierField(),
                    'layout_update_xml',
                    $layoutUpdateXmlFieldQueryModifier
                ),
                new FieldToConvert(
                    LayoutUpdateConverter::class,
                    $this->moduleDataSetup->getTable('cms_page'),
                    $pageMetadata->getIdentifierField(),
                    'custom_layout_update_xml',
                    $customLayoutUpdateXmlFieldQueryModifier
                ),
            ],
            $this->moduleDataSetup->getConnection()
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
            UpdatePrivacyPolicyPage::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.0.2';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
