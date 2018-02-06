<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Search\FilterMapper;

use Magento\CatalogSearch\Model\Adapter\Mysql\Filter\AliasResolver;
use Magento\CatalogSearch\Model\Search\FilterMapper\TermDropdownStrategy\AddJoinToSelect;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\App\ObjectManager;

/**
 * This strategy handles attributes which comply with two criteria:
 *   - The filter for dropdown or multi-select attribute
 *   - The filter is Term filter
 */
class TermDropdownStrategy implements FilterStrategyInterface
{
    /**
     * @var AliasResolver
     */
    private $aliasResolver;

    /**
     * @var EavConfig
     */
    private $eavConfig;

    /**
     * @var AddJoinToSelect
     */
    private $addJoinToSelect;

    /**
     * @param EavConfig $eavConfig
     * @param AliasResolver $aliasResolver
     * @param AddJoinToSelect $addJoinToSelect
     */
    public function __construct(
        EavConfig $eavConfig,
        AliasResolver $aliasResolver,
        AddJoinToSelect $addJoinToSelect = null
    ) {
        $this->eavConfig = $eavConfig;
        $this->aliasResolver = $aliasResolver;
        $this->addJoinToSelect = $addJoinToSelect ?: ObjectManager::getInstance()->get(AddJoinToSelect::class);
    }

    /**
     * {@inheritDoc}
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function apply(
        \Magento\Framework\Search\Request\FilterInterface $filter,
        \Magento\Framework\DB\Select $select
    ) {
        $alias = $this->aliasResolver->getAlias($filter);
        $attribute = $this->getAttributeByCode($filter->getField());
        $this->addJoinToSelect->execute((int)$attribute->getId(), $alias, $select);

        return true;
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
