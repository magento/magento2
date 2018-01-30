<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Search\FilterMapper;

use Magento\CatalogSearch\Model\Adapter\Mysql\Filter\AliasResolver;
use Magento\CatalogSearch\Model\Search\FilterMapper\TermDropdownStrategy\JoinAdderToSelect;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;

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
     * @var JoinAdderToSelect
     */
    private $joinAdderToSelect;

    /**
     * @param StoreManagerInterface $storeManager
     * @param ResourceConnection $resourceConnection
     * @param EavConfig $eavConfig
     * @param ScopeConfigInterface $scopeConfig
     * @param AliasResolver $aliasResolver
     * @param JoinAdderToSelect $joinAdderToSelect
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ResourceConnection $resourceConnection,
        EavConfig $eavConfig,
        ScopeConfigInterface $scopeConfig,
        AliasResolver $aliasResolver,
        JoinAdderToSelect $joinAdderToSelect = null
    ) {
        $this->eavConfig = $eavConfig;
        $this->aliasResolver = $aliasResolver;
        $this->joinAdderToSelect = $joinAdderToSelect ?: ObjectManager::getInstance()->get(JoinAdderToSelect::class);
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
        $this->joinAdderToSelect->execute((int)$attribute->getId(), $alias, $select);

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
