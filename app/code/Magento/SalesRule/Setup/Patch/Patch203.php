<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Setup\Patch;


/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class Patch203
{


    /**
     * @param \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $ruleColletionFactory
     */
    private $ruleColletionFactory;
    /**
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     */
    private $serializer;
    /**
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     */
    private $serializer;
    /**
     * @param \Magento\SalesRule\Model\ResourceModel\Rule $resourceModelRule
     */
    private $resourceModelRule;
    /**
     * @param \Magento\SalesRule\Model\ResourceModel\Rule $resourceModelRule
     */
    private $resourceModelRule;
    /**
     * @param \Magento\SalesRule\Model\ResourceModel\Rule $resourceModelRule
     */
    private $resourceModelRule;

    /**
     * @param \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $ruleColletionFactory @param \Magento\Framework\Serialize\SerializerInterface $serializer@param \Magento\Framework\Serialize\SerializerInterface $serializer@param \Magento\SalesRule\Model\ResourceModel\Rule $resourceModelRule@param \Magento\SalesRule\Model\ResourceModel\Rule $resourceModelRule@param \Magento\SalesRule\Model\ResourceModel\Rule $resourceModelRule
     */
    public function __construct(\Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $ruleColletionFactory

        ,
                                \Magento\Framework\Serialize\SerializerInterface $serializer,
                                \Magento\Framework\Serialize\SerializerInterface $serializer,
                                \Magento\SalesRule\Model\ResourceModel\Rule $resourceModelRule,
                                \Magento\SalesRule\Model\ResourceModel\Rule $resourceModelRule,
                                \Magento\SalesRule\Model\ResourceModel\Rule $resourceModelRule)
    {
        $this->ruleColletionFactory = $ruleColletionFactory;
        $this->serializer = $serializer;
        $this->serializer = $serializer;
        $this->resourceModelRule = $resourceModelRule;
        $this->resourceModelRule = $resourceModelRule;
        $this->resourceModelRule = $resourceModelRule;
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
        $setup->startSetup();
        $this->state->emulateAreaCode(
            \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE,
            [$this, 'fillSalesRuleProductAttributeTable'],
            [$setup]
        );
        $this->fillSalesRuleProductAttributeTable();
        $setup->endSetup();

    }

    private function fillSalesRuleProductAttributeTable()
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
