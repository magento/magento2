<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Search\FilterMapper;

use Magento\CatalogSearch\Model\Adapter\Mysql\Filter\AliasResolver;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;

/**
 * FilterContext represents a Context of the Strategy pattern
 * Its responsibility is to choose appropriate strategy to apply passed filter to the Select
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.2.0
 */
class FilterContext implements FilterStrategyInterface
{
    /**
     * @var ExclusionStrategy
     * @since 2.2.0
     */
    private $exclusionStrategy;

    /**
     * @var EavConfig
     * @since 2.2.0
     */
    private $eavConfig;

    /**
     * @var StaticAttributeStrategy
     * @since 2.2.0
     */
    private $staticAttributeStrategy;

    /**
     * @param EavConfig $eavConfig
     * @param AliasResolver $aliasResolver
     * @param ExclusionStrategy $exclusionStrategy
     * @param TermDropdownStrategy $termDropdownStrategy
     * @param StaticAttributeStrategy $staticAttributeStrategy
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.2.0
     */
    public function __construct(
        EavConfig $eavConfig,
        AliasResolver $aliasResolver,
        ExclusionStrategy $exclusionStrategy,
        TermDropdownStrategy $termDropdownStrategy,
        StaticAttributeStrategy $staticAttributeStrategy
    ) {
        $this->eavConfig = $eavConfig;
        $this->exclusionStrategy = $exclusionStrategy;
        $this->staticAttributeStrategy = $staticAttributeStrategy;
    }

    /**
     * {@inheritDoc}
     * @since 2.2.0
     */
    public function apply(
        \Magento\Framework\Search\Request\FilterInterface $filter,
        \Magento\Framework\DB\Select $select
    ) {
        $isApplied = $this->exclusionStrategy->apply($filter, $select);

        if (!$isApplied) {
            $attribute = $this->getAttributeByCode($filter->getField());
            if ($attribute) {
                if ($filter->getType() === \Magento\Framework\Search\Request\FilterInterface::TYPE_TERM
                    && in_array($attribute->getFrontendInput(), ['select', 'multiselect'], true)
                ) {
                    $isApplied = false;
                } elseif ($attribute->getBackendType() === AbstractAttribute::TYPE_STATIC) {
                    $isApplied = $this->staticAttributeStrategy->apply($filter, $select);
                }
            }
        }

        return $isApplied;
    }

    /**
     * @param string $field
     * @return \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.2.0
     */
    private function getAttributeByCode($field)
    {
        return $this->eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $field);
    }
}
