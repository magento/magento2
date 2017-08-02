<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Plugin\Indexer\Product;

use Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor;
use Magento\CatalogRule\Model\ResourceModel\Rule\CollectionFactory as RuleCollectionFactory;
use Magento\CatalogRule\Model\Rule;
use Magento\CatalogRule\Model\Rule\Condition\Combine;
use Magento\Framework\Message\ManagerInterface;
use Magento\Rule\Model\Condition\Product\AbstractProduct;

/**
 * Class \Magento\CatalogRule\Plugin\Indexer\Product\Attribute
 *
 * @since 2.0.0
 */
class Attribute
{
    /**
     * @var RuleCollectionFactory
     * @since 2.0.0
     */
    protected $ruleCollectionFactory;

    /**
     * @var RuleProductProcessor
     * @since 2.0.0
     */
    protected $ruleProductProcessor;

    /**
     * @var ManagerInterface
     * @since 2.0.0
     */
    protected $messageManager;

    /**
     * @param RuleCollectionFactory $ruleCollectionFactory
     * @param RuleProductProcessor $ruleProductProcessor
     * @param ManagerInterface $messageManager
     * @since 2.0.0
     */
    public function __construct(
        RuleCollectionFactory $ruleCollectionFactory,
        RuleProductProcessor $ruleProductProcessor,
        ManagerInterface $messageManager
    ) {
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->ruleProductProcessor = $ruleProductProcessor;
        $this->messageManager = $messageManager;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $subject
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute
     * @return \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function afterSave(
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $subject,
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute
    ) {
        if ($attribute->dataHasChangedFor('is_used_for_promo_rules') && !$attribute->getIsUsedForPromoRules()) {
            $this->checkCatalogRulesAvailability($attribute->getAttributeCode());
        }
        return $attribute;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $subject
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute
     * @return \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function afterDelete(
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $subject,
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute
    ) {
        if ($attribute->getIsUsedForPromoRules()) {
            $this->checkCatalogRulesAvailability($attribute->getAttributeCode());
        }
        return $attribute;
    }

    /**
     * Check rules that contains affected attribute
     * If rules were found they will be set to inactive and notice will be add to admin session
     *
     * @param string $attributeCode
     * @return $this
     * @since 2.0.0
     */
    protected function checkCatalogRulesAvailability($attributeCode)
    {
        /* @var $collection RuleCollectionFactory */
        $collection = $this->ruleCollectionFactory->create()->addAttributeInConditionFilter($attributeCode);

        $disabledRulesCount = 0;
        foreach ($collection as $rule) {
            /* @var $rule Rule */
            $rule->setIsActive(0);
            /* @var $rule->getConditions() Combine */
            $this->removeAttributeFromConditions($rule->getConditions(), $attributeCode);
            $rule->save();

            $disabledRulesCount++;
        }

        if ($disabledRulesCount) {
            $this->ruleProductProcessor->markIndexerAsInvalid();
            $this->messageManager->addWarning(
                __(
                    'You disabled %1 Catalog Price Rules based on "%2" attribute.',
                    $disabledRulesCount,
                    $attributeCode
                )
            );
        }

        return $this;
    }

    /**
     * Remove catalog attribute condition by attribute code from rule conditions
     *
     * @param Combine $combine
     * @param string $attributeCode
     * @return void
     * @since 2.0.0
     */
    protected function removeAttributeFromConditions(Combine $combine, $attributeCode)
    {
        $conditions = $combine->getConditions();
        foreach ($conditions as $conditionId => $condition) {
            if ($condition instanceof Combine) {
                $this->removeAttributeFromConditions($condition, $attributeCode);
            }
            if ($condition instanceof AbstractProduct) {
                if ($condition->getAttribute() == $attributeCode) {
                    unset($conditions[$conditionId]);
                }
            }
        }
        $combine->setConditions($conditions);
    }
}
