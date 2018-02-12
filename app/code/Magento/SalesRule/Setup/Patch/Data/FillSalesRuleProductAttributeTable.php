<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Setup\Patch\Data;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\State;
use Magento\Setup\Model\Patch\DataPatchInterface;
use Magento\Setup\Model\Patch\PatchVersionInterface;

/**
 * Class FillSalesRuleProductAttributeTable
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
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var State
     */
    private $appState;

    /**
     * FillSalesRuleProductAttributeTable constructor.
     * @param \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $ruleColletionFactory
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param \Magento\SalesRule\Model\ResourceModel\Rule $resourceModelRule
     * @param ResourceConnection $resourceConnection
     * @param State $appState
     */
    public function __construct(
        \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $ruleColletionFactory,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\SalesRule\Model\ResourceModel\Rule $resourceModelRule,
        ResourceConnection $resourceConnection,
        State $appState
    ) {
        $this->ruleColletionFactory = $ruleColletionFactory;
        $this->serializer = $serializer;
        $this->resourceModelRule = $resourceModelRule;
        $this->resourceConnection = $resourceConnection;
        $this->appState = $appState;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->resourceConnection->getConnection()->startSetup();
            $this->appState->emulateAreaCode(
                \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE,
                [$this, 'fillSalesRuleProductAttributeTable']
            );
            $this->fillSalesRuleProductAttributeTable();
        $this->resourceConnection->getConnection()->endSetup();

    }

    /**
     * Fill attribute table for sales rule
     */
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

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
            ConvertSerializedDataToJson::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
        return '2.0.3';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
