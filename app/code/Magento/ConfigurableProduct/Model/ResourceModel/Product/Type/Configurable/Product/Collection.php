<?php
/**
 * Catalog super product link collection
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product;

/**
 * Collection of configurable product variation
 *
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 * @since 100.0.2
 */
class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{
    /**
     * Link table name
     *
     * @var string
     */
    protected $_linkTable;

    /**
     * @var \Magento\Catalog\Model\Product[]
     */
    private $products = [];

    /**
     * Assign link table name
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_linkTable = $this->getTable('catalog_product_super_link');
    }

    /**
     * Init select
     *
     * @return $this|\Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product\Collection
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        $this->getSelect()->join(
            ['link_table' => $this->_linkTable],
            'link_table.product_id = e.entity_id',
            ['parent_id']
        );

        return $this;
    }

    /**
     * Set Product filter to result
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return $this
     */
    public function setProductFilter($product)
    {
        $this->products[] = $product;
        return $this;
    }

    /**
     * Set the collection with a list of products as filter so that we can return all the linked product for a list of products
     *
     * @param array $products
     * @return $this
     */
    public function setProductListFilter(array $products)
    {
        $this->products = $products;
        return $this;
    }

    /**
     * Add parent ids to `in` filter before load.
     *
     * @return $this
     * @since 100.3.0
     */
    protected function _renderFilters()
    {
        parent::_renderFilters();
        $metadata = $this->getProductEntityMetadata();
        $parentIds = [];
        foreach ($this->products as $product) {
            $parentIds[] = $product->getData($metadata->getLinkField());
        }

        $this->getSelect()->where('link_table.parent_id in (?)', $parentIds, \Zend_Db::INT_TYPE);

        return $this;
    }

    /**
     * Retrieve is flat enabled flag. Return always false if magento run admin
     *
     * @return bool
     */
    public function isEnabledFlat()
    {
        return false;
    }
}
