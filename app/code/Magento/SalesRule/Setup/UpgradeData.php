<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Setup;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Framework\App\State;
use Magento\Framework\DB\AggregatedFieldDataConverter;
use Magento\Framework\DB\DataConverter\SerializedToJson;
use Magento\Framework\DB\FieldToConvert;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\SalesRule\Api\Data\RuleInterface;
use Magento\SalesRule\Model\ResourceModel\Rule as ResourceModelRule;
use Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory as RuleCollectionFactory;
use Magento\SalesRule\Model\Rule as ModelRule;

/**
 * Class \Magento\SalesRule\Setup\UpgradeData
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var AggregatedFieldDataConverter
     */
    private $aggregatedFieldConverter;

    /**
     * Resource Model of sales rule.
     *
     * @var ResourceModelRule;
     */
    private $resourceModelRule;

    /**
     * App state.
     *
     * @var State
     */
    private $state;

    /**
     * Serializer.
     *
     * @var Json
     */
    private $serializer;

    /**
     * Rule Collection Factory.
     *
     * @var RuleColletionFactory
     */
    private $ruleColletionFactory;

    /**
     * @param AggregatedFieldDataConverter $aggregatedFieldConverter
     * @param MetadataPool $metadataPool
     * @param ResourceModelRule $resourceModelRule
     * @param Json $serializer
     * @param State $state
     * @param RuleCollectionFactory $ruleColletionFactory
     */
    public function __construct(
        AggregatedFieldDataConverter $aggregatedFieldConverter,
        MetadataPool $metadataPool,
        ResourceModelRule $resourceModelRule,
        Json $serializer,
        State $state,
        RuleCollectionFactory $ruleColletionFactory
    ) {
        $this->aggregatedFieldConverter = $aggregatedFieldConverter;
        $this->metadataPool = $metadataPool;
        $this->resourceModelRule = $resourceModelRule;
        $this->serializer = $serializer;
        $this->state = $state;
        $this->ruleColletionFactory = $ruleColletionFactory;
    }

    /**
     * @inheritdoc
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '2.0.2', '<')) {
            $this->convertSerializedDataToJson($setup);
        }
        if (version_compare($context->getVersion(), '2.0.3', '<')) {
            $this->state->emulateAreaCode(
                FrontNameResolver::AREA_CODE,
                [$this, 'fillSalesRuleProductAttributeTable'],
                [$setup]
            );
            $this->fillSalesRuleProductAttributeTable();
        }
        $setup->endSetup();
    }

    /**
     * Convert metadata from serialized to JSON format:
     *
     * @param ModuleDataSetupInterface $setup
     *
     * @return void
     */
    public function convertSerializedDataToJson($setup)
    {
        $metadata = $this->metadataPool->getMetadata(RuleInterface::class);
        $this->aggregatedFieldConverter->convert(
            [
                new FieldToConvert(
                    SerializedToJson::class,
                    $setup->getTable('salesrule'),
                    $metadata->getLinkField(),
                    'conditions_serialized'
                ),
                new FieldToConvert(
                    SerializedToJson::class,
                    $setup->getTable('salesrule'),
                    $metadata->getLinkField(),
                    'actions_serialized'
                ),
            ],
            $setup->getConnection()
        );
    }

    /**
     * Fills blank table salesrule_product_attribute with data.
     *
     * @return void
     */
    public function fillSalesRuleProductAttributeTable()
    {
        /** @var ResourceModelRule\Collection $ruleCollection */
        $ruleCollection = $this->ruleColletionFactory->create();
        /** @var ModelRule $rule */
        foreach ($ruleCollection as $rule) {
            // Save product attributes used in rule
            $conditions = $rule->getConditions()->asArray();
            $actions = $rule->getActions()->asArray();
            $serializedConditions = $this->serializer->serialize($conditions);
            $serializedActions = $this->serializer->serialize($actions);
            $conditionAttributes = $this->resourceModelRule->getProductAttributes($serializedConditions);
            $actionAttributes = $this->resourceModelRule->getProductAttributes($serializedActions);
            $ruleProductAttributes = array_merge($conditionAttributes, $actionAttributes);
            if ($ruleProductAttributes) {
                $this->resourceModelRule->setActualProductAttributes($rule, $ruleProductAttributes);
            }
        }
    }
}
