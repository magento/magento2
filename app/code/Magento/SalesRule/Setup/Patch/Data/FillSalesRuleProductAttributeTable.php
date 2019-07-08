<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Setup\Patch\Data;

use Magento\Framework\App\State;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Class FillSalesRuleProductAttributeTable
 *
 * @package Magento\SalesRule\Setup\Patch
 */
class FillSalesRuleProductAttributeTable implements DataPatchInterface, PatchVersionInterface
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
     * @param \Magento\SalesRule\Model\ResourceModel\Rule $resourceModelRule
     */
    private $resourceModelRule;

    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var State
     */
    private $appState;

    /**
     * FillSalesRuleProductAttributeTable constructor.
     * @param \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $ruleColletionFactory
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param \Magento\SalesRule\Model\ResourceModel\Rule $resourceModelRule
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     * @param State $appState
     */
    public function __construct(
        \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $ruleColletionFactory,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\SalesRule\Model\ResourceModel\Rule $resourceModelRule,
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup,
        State $appState
    ) {
        $this->ruleColletionFactory = $ruleColletionFactory;
        $this->serializer = $serializer;
        $this->resourceModelRule = $resourceModelRule;
        $this->moduleDataSetup = $moduleDataSetup;
        $this->appState = $appState;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
            $this->appState->emulateAreaCode(
                \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE,
                [$this, 'fillSalesRuleProductAttributeTable']
            );
            $this->fillSalesRuleProductAttributeTable();
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * Fill attribute table for sales rule
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

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [
            ConvertSerializedDataToJson::class
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getVersion()
    {
        return '2.0.3';
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
