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
namespace Magento\CatalogSearch\Model\Adapter\Mysql\Plugin\Aggregation\Category;

use Magento\Framework\Search\Request\BucketInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\App\Resource;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Search\Request\Dimension;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Catalog\Model\Layer\Resolver;

class DataProvider
{
    /**
     * @var Resource
     */
    private $resource;

    /**
     * @var ScopeResolverInterface
     */
    private $scopeResolver;

    /**
     * Category factory
     *
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @param Resource $resource
     * @param ScopeResolverInterface $scopeResolver
     * @param Resolver $layerResolver
     */
    public function __construct(
        Resource $resource,
        ScopeResolverInterface $scopeResolver,
        Resolver $layerResolver
    ) {
        $this->resource = $resource;
        $this->scopeResolver = $scopeResolver;
        $this->layer = $layerResolver->get();
    }

    /**
     * @param \Magento\CatalogSearch\Model\Adapter\Mysql\Aggregation\DataProvider $subject
     * @param callable $proceed
     * @param BucketInterface $bucket
     * @param Dimension[] $dimensions
     *
     * @return Select
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetDataSet(
        \Magento\CatalogSearch\Model\Adapter\Mysql\Aggregation\DataProvider $subject,
        \Closure $proceed,
        BucketInterface $bucket,
        array $dimensions
    ) {
        if ($bucket->getField() == 'category_ids') {
            $currentScope = $dimensions['scope']->getValue();
            $currentScopeId = $this->scopeResolver->getScope($currentScope)->getId();
            $currenCategory = $this->layer->getCurrentCategory();

            $derivedTable = $this->getSelect();
            $derivedTable->from(
                ['main_table' => $this->resource->getTableName('catalog_category_product_index')],
                [
                    'entity_id' => 'product_id',
                    'value' => 'category_id'
                ]
            )->where('main_table.store_id = ?', $currentScopeId);

            if (!empty($currenCategory)) {
                $derivedTable->join(
                    array('category' => $this->resource->getTableName('catalog_category_entity')),
                    'main_table.category_id = category.entity_id',
                    array()
                )->where('`category`.`path` LIKE ?', $currenCategory->getPath() . '%')
                ->where('`category`.`level` > ?', $currenCategory->getLevel());
            }
            $select = $this->getSelect();
            $select->from(['main_table' => $derivedTable]);
            return $select;
        }
        return $proceed($bucket, $dimensions);
    }

    /**
     * @return Select
     */
    private function getSelect()
    {
        return $this->getConnection()->select();
    }

    /**
     * @return AdapterInterface
     */
    private function getConnection()
    {
        return $this->resource->getConnection(Resource::DEFAULT_READ_RESOURCE);
    }
}
