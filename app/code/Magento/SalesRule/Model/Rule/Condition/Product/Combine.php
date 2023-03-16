<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Rule\Condition\Product;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\Model\AbstractModel;
use Magento\Rule\Model\Condition\Combine as ConditionCombine;
use Magento\Rule\Model\Condition\Context as ConditionContext;
use Magento\SalesRule\Model\Rule\Condition\Product as RuleCondProduct;
use Magento\SalesRule\Model\Rule\Condition\Product\Combine as RuleCondProductCombine;

/**
 * Combine conditions for product.
 * @api
 * @since 100.0.2
 */
class Combine extends ConditionCombine
{
    /**
     * @var RuleCondProduct
     */
    protected $_ruleConditionProd;

    /**
     * @param ConditionContext $context
     * @param RuleCondProduct $ruleConditionProduct
     * @param array $data
     */
    public function __construct(
        ConditionContext $context,
        RuleCondProduct $ruleConditionProduct,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_ruleConditionProd = $ruleConditionProduct;
        $this->setType(RuleCondProductCombine::class);
    }

    /**
     * Get new child select options
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $productAttributes = $this->_ruleConditionProd->loadAttributeOptions()->getAttributeOption();
        $pAttributes = [];
        $iAttributes = [];
        foreach ($productAttributes as $code => $label) {
            if (strpos($code, 'quote_item_') === 0 || strpos($code, 'parent::quote_item_') === 0) {
                $iAttributes[] = [
                    'value' => RuleCondProduct::class . '|' . $code,
                    'label' => $label,
                ];
            } else {
                $pAttributes[] = [
                    'value' => RuleCondProduct::class . '|' . $code,
                    'label' => $label,
                ];
            }
        }

        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive(
            $conditions,
            [
                [
                    'value' => RuleCondProductCombine::class,
                    'label' => __('Conditions Combination'),
                ],
                ['label' => __('Cart Item Attribute'), 'value' => $iAttributes],
                ['label' => __('Product Attribute'), 'value' => $pAttributes]
            ]
        );
        return $conditions;
    }

    /**
     * Collect validated attributes
     *
     * @param Collection $productCollection
     * @return $this
     */
    public function collectValidatedAttributes($productCollection)
    {
        foreach ($this->getConditions() as $condition) {
            $condition->collectValidatedAttributes($productCollection);
        }
        return $this;
    }

    /**
     * @inheritdoc
     * @since 101.0.6
     */
    protected function _isValid($entity)
    {
        if (!$this->getConditions()) {
            return true;
        }

        $all = $this->getAggregator() === 'all';
        $true = (bool)$this->getValue();

        foreach ($this->getConditions() as $cond) {
            if ($entity instanceof AbstractModel) {
                $validated = $this->validateEntity($cond, $entity);
            } else {
                $validated = $cond->validateByEntityId($entity);
            }
            if ($all && $validated !== $true) {
                return false;
            } elseif (!$all && $validated === $true) {
                return true;
            }
        }
        return $all ? true : false;
    }

    /**
     * Validate entity.
     *
     * @param object $cond
     * @param AbstractModel $entity
     * @return bool
     */
    private function validateEntity($cond, AbstractModel $entity)
    {
        $true = (bool)$this->getValue();
        $validated = !$true;
        foreach ($this->retrieveValidateEntities($cond->getAttributeScope(), $entity) as $validateEntity) {
            $validated = $cond->validate($validateEntity);
            if ($validated === $true) {
                break;
            }
        }

        return $validated;
    }

    /**
     * Retrieve entities for validation by attribute scope
     *
     * @param string $attributeScope
     * @param AbstractModel $entity
     * @return AbstractModel[]
     */
    private function retrieveValidateEntities($attributeScope, AbstractModel $entity)
    {
        if ($attributeScope === 'parent') {
            $parentItem = $entity->getParentItem();
            $validateEntities = $parentItem ? [$parentItem] : [$entity];
        } elseif ($attributeScope === 'children') {
            $validateEntities = $entity->getChildren() ?: [$entity];
        } else {
            $validateEntities = $entity->getChildren() ?: [];
            $validateEntities[] = $entity;
        }

        return $validateEntities;
    }
}
