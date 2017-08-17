<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Setup;

/**
 * Class \Magento\SalesRule\Setup\UpgradeData
 */
class UpgradeData implements \Magento\Framework\Setup\UpgradeDataInterface
{
    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    private $metadataPool;

    /**
     * @var \Magento\Framework\DB\AggregatedFieldDataConverter
     */
    private $aggregatedFieldConverter;

    /**
     * Resource Model of sales rule.
     *
     * @var \Magento\SalesRule\Model\ResourceModel\Rule;
     */
    private $resourceModelRule;

    /**
     * App state.
     *
     * @var \Magento\Framework\App\State
     */
    private $state;

    /**
     * Serializer.
     *
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    /**
     * Rule Collection Factory.
     *
     * @var \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory
     */
    private $ruleColletionFactory;

    /**
     * @param \Magento\Framework\DB\AggregatedFieldDataConverter $aggregatedFieldConverter
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     * @param \Magento\SalesRule\Model\ResourceModel\Rule $resourceModelRule
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
     * @param \Magento\Framework\App\State $state
     * @param \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $ruleColletionFactory
     */
    public function __construct(
        \Magento\Framework\DB\AggregatedFieldDataConverter $aggregatedFieldConverter,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        \Magento\SalesRule\Model\ResourceModel\Rule $resourceModelRule,
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        \Magento\Framework\App\State $state,
        \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $ruleColletionFactory
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
    public function upgrade(
        \Magento\Framework\Setup\ModuleDataSetupInterface $setup,
        \Magento\Framework\Setup\ModuleContextInterface $context
    ) {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '2.0.2', '<')) {
            $this->convertSerializedDataToJson($setup);
        }
        if (version_compare($context->getVersion(), '2.0.3', '<')) {
            $this->state->emulateAreaCode(
                \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE,
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
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $setup     *
     * @return void
     */
    public function convertSerializedDataToJson($setup)
    {
        $metadata = $this->metadataPool->getMetadata(\Magento\SalesRule\Api\Data\RuleInterface::class);
        $this->aggregatedFieldConverter->convert(
            [
                new \Magento\Framework\DB\FieldToConvert(
                    \Magento\Framework\DB\DataConverter\SerializedToJson::class,
                    $setup->getTable('salesrule'),
                    $metadata->getLinkField(),
                    'conditions_serialized'
                ),
                new \Magento\Framework\DB\FieldToConvert(
                    \Magento\Framework\DB\DataConverter\SerializedToJson::class,
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
        /** @var \Magento\SalesRule\Model\ResourceModel\Rule\Collection $ruleCollection */
        $ruleCollection = $this->ruleColletionFactory->create();
        /** @var \Magento\SalesRule\Model\Rule $rule */
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
