<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Search\FilterMapper;

use Magento\CatalogSearch\Model\Adapter\Mysql\Filter\AliasResolver;
use Magento\CatalogSearch\Model\Search\FilterMapper\TermDropdownStrategy\SelectBuilder;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\App\ObjectManager;

/**
 * This strategy handles attributes which comply with two criteria:
 *   - The filter for dropdown or multi-select attribute
 *   - The filter is Term filter
 *
 * @deprecated 101.0.0
 * @see \Magento\ElasticSearch
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
     * @var SelectBuilder
     */
    private $selectBuilder;

    /**
     * @param null $storeManager @deprecated
     * @param null $resourceConnection @deprecated
     * @param EavConfig $eavConfig
     * @param null $scopeConfig @deprecated
     * @param AliasResolver $aliasResolver
     * @param SelectBuilder|null $selectBuilder
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        $storeManager,
        $resourceConnection,
        EavConfig $eavConfig,
        $scopeConfig,
        AliasResolver $aliasResolver,
        SelectBuilder $selectBuilder = null
    ) {
        $this->eavConfig = $eavConfig;
        $this->aliasResolver = $aliasResolver;
        $this->selectBuilder = $selectBuilder ?: ObjectManager::getInstance()->get(SelectBuilder::class);
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
        $this->selectBuilder->execute((int)$attribute->getId(), $alias, $select);

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
