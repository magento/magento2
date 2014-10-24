<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\CatalogSearch\Model\Adapter\Mysql\Filter;

use Magento\Eav\Model\Config;
use Magento\Framework\App\Resource;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Search\Adapter\Mysql\ConditionManager;
use Magento\Framework\Search\Adapter\Mysql\Filter\PreprocessorInterface;
use Magento\Framework\Search\Request\FilterInterface;

class Preprocessor implements PreprocessorInterface
{
    /**
     * @var ConditionManager
     */
    private $conditionManager;

    /**
     * @var ScopeResolverInterface
     */
    private $scopeResolver;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Resource
     */
    private $resource;

    /**
     * @param ConditionManager $conditionManager
     * @param ScopeResolverInterface $scopeResolver
     * @param Config $config
     * @param Resource $resource
     */
    public function __construct(
        ConditionManager $conditionManager,
        ScopeResolverInterface $scopeResolver,
        Config $config,
        Resource $resource
    ) {
        $this->conditionManager = $conditionManager;
        $this->scopeResolver = $scopeResolver;
        $this->config = $config;
        $this->resource = $resource;
    }

    /**
     * {@inheritdoc}
     */
    public function process(FilterInterface $filter, $isNegation, $query)
    {
        return $resultQuery =  $this->processQueryWithField($filter, $isNegation, $query);
    }

    /**
     * @param FilterInterface $filter
     * @param bool $isNegation
     * @param string $query
     * @return string
     */
    private function processQueryWithField(FilterInterface $filter, $isNegation, $query)
    {
        $currentStoreId = $this->scopeResolver->getScope()->getId();

        $attribute = $this->config->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $filter->getField());
        $select = $this->getSelect();
        $table = $attribute->getBackendTable();
        if ($filter->getField() == 'price') {
            $query = str_replace('price', 'min_price', $query);
            $select->from(['main_table' => $this->resource->getTableName('catalog_product_index_price')], 'entity_id')
                ->where($query);
        } else {
            if ($attribute->isStatic()) {
                $select->from(['main_table' => $table], 'entity_id')
                    ->where($query);
            } else {

                $ifNullCondition = $this->getConnection()->getIfNullSql('current_store.value', 'main_table.value');

                $select->from(['main_table' => $table], 'entity_id')
                    ->joinLeft(
                        ['current_store' => $table],
                        'current_store.attribute_id = main_table.attribute_id AND current_store.store_id = '
                        . $currentStoreId,
                        null
                    )
                    ->columns([$filter->getField() => $ifNullCondition])
                    ->where(
                        'main_table.attribute_id = ?',
                        $attribute->getAttributeId()
                    )
                    ->where('main_table.store_id = ?', \Magento\Store\Model\Store::DEFAULT_STORE_ID)
                    ->having($query);
            }
        }

        return 'product_id ' . ' IN (
                select entity_id from  ' . $this->conditionManager->wrapBrackets($select) . '
             as filter)';
    }

    /**
     * @return AdapterInterface
     */
    private function getConnection()
    {
        return $this->resource->getConnection(Resource::DEFAULT_READ_RESOURCE);
    }

    /**
     * @return \Magento\Framework\DB\Select
     */
    private function getSelect()
    {
        return $this->getConnection()->select();
    }
}
