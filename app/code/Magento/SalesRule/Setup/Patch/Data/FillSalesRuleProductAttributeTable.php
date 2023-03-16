<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Setup\Patch\Data;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Framework\App\State;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\SalesRule\Model\ResourceModel\Rule as ResourceRule;
use Magento\SalesRule\Model\ResourceModel\Rule\Collection as RuleCollection;
use Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory as RuleCollectionFactory;
use Magento\SalesRule\Model\Rule as ModelRule;

/**
 * Class FillSalesRuleProductAttributeTable
 *
 * @package Magento\SalesRule\Setup\Patch
 */
class FillSalesRuleProductAttributeTable implements DataPatchInterface, PatchVersionInterface
{
    /**
     * FillSalesRuleProductAttributeTable constructor.
     * @param RuleCollectionFactory $ruleColletionFactory
     * @param SerializerInterface $serializer
     * @param ResourceRule $resourceModelRule
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param State $appState
     */
    public function __construct(
        private readonly RuleCollectionFactory $ruleColletionFactory,
        private readonly SerializerInterface $serializer,
        private readonly ResourceRule $resourceModelRule,
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly State $appState
    ) {
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
            $this->appState->emulateAreaCode(
                FrontNameResolver::AREA_CODE,
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
        /** @var RuleCollection $ruleCollection */
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
