<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Search\FilterMapper;


use Magento\CatalogSearch\Model\Adapter\Mysql\Filter\AliasResolver;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;

/**
 * FilterContext represents a Context of the Strategy pattern
 * Its responsibility is to choose appropriate strategy to apply passed filter to the Select
 */
class FilterContext implements FilterStrategyInterface
{
    /**
     * @var ExclusionStrategy
     */
    private $exclusionStrategy;

    /**
     * @var EavConfig
     */
    private $eavConfig;

    /**
     * @var TermDropdownStrategy
     */
    private $termDropdownStrategy;

    /**
     * @var StaticAttributeStrategy
     */
    private $staticAttributeStrategy;

    /**
     * @var AliasResolver
     */
    private $aliasResolver;

    /**
     * @param EavConfig $eavConfig
     * @param AliasResolver $aliasResolver
     * @param ExclusionStrategy $exclusionStrategy
     * @param TermDropdownStrategy $termDropdownStrategy
     * @param StaticAttributeStrategy $staticAttributeStrategy
     */
    public function __construct(
        EavConfig $eavConfig,
        AliasResolver $aliasResolver,
        ExclusionStrategy $exclusionStrategy,
        TermDropdownStrategy $termDropdownStrategy,
        StaticAttributeStrategy $staticAttributeStrategy
    ) {
        $this->eavConfig = $eavConfig;
        $this->aliasResolver = $aliasResolver;
        $this->exclusionStrategy = $exclusionStrategy;
        $this->termDropdownStrategy = $termDropdownStrategy;
        $this->staticAttributeStrategy = $staticAttributeStrategy;
    }

    /**
     * {@inheritDoc}
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
                    $isApplied = $this->termDropdownStrategy->apply($filter, $select);
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
     */
    private function getAttributeByCode($field)
    {
        return $this->eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $field);
    }
}
