<?php
/**
 * Catalog super product link collection
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product;

/**
 * Class Collection
 *
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
    private $products;

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
     * Add parent ids to `in` filter before load.
     *
     * @return $this
     */
    protected function _beforeLoad()
    {
        parent::_beforeLoad();
        $metadata = $this->getProductEntityMetadata();
        $parentIds = [];
        foreach ($this->products as $product) {
            $parentIds[] = $product->getData($metadata->getLinkField());
        }

        $this->getSelect()->where('link_table.parent_id in (?)', $parentIds);

        return $this;
    }

    /**
     * Retrieve is flat enabled flag
     * Return always false if magento run admin
     *
     * @return bool
     */
    public function isEnabledFlat()
    {
        return false;
    }
}
