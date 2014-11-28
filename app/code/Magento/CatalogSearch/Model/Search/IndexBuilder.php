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

namespace Magento\CatalogSearch\Model\Search;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Resource;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Mysql\IndexBuilderInterface;
use Magento\Framework\Search\RequestInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Build base Query for Index
 */
class IndexBuilder implements IndexBuilderInterface
{
    /**
     * @var Resource
     */
    private $resource;

    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param ScopeConfigInterface $config
     */
    public function __construct(Resource $resource, ScopeConfigInterface $config)
    {
        $this->resource = $resource;
        $this->config = $config;
    }

    /**
     * Build index query
     *
     * @param RequestInterface $request
     * @return Select
     */
    public function build(RequestInterface $request)
    {
        $select = $this->getSelect()
            ->from(
                ['search_index' => $this->resource->getTableName($request->getIndex())],
                ['entity_id' => 'search_index.product_id']
            )
            ->joinLeft(
                ['category_index' => $this->resource->getTableName('catalog_category_product_index')],
                'search_index.product_id = category_index.product_id'
                . ' AND search_index.store_id = category_index.store_id',
                []
            );


        $isShowOutOfStock = $this->config->isSetFlag(
            'cataloginventory/options/show_out_of_stock',
            ScopeInterface::SCOPE_STORE
        );
        if ($isShowOutOfStock === false) {
            $select->joinLeft(
                ['stock_index' => $this->resource->getTableName('cataloginventory_stock_status')],
                'search_index.product_id = stock_index.product_id'
                . ' AND stock_index.website_id = 1 AND stock_index.stock_id = 1',
                []
            )
                ->where('stock_index.stock_status = ?', 1);
        }

        return $select;
    }

    /**
     * Get empty Select
     *
     * @return Select
     */
    private function getSelect()
    {
        return $this->resource->getConnection(Resource::DEFAULT_READ_RESOURCE)
            ->select();
    }
}
