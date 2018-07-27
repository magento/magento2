<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Block\Product;

/**
 * Reports Recently Products Abstract Block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
abstract class AbstractProduct extends \Magento\Catalog\Block\Product\AbstractProduct
{
    /**
     * Product Index model type
     *
     * @var string
     */
    protected $_indexType;

    /**
     * Product Index Collection
     *
     * @var \Magento\Reports\Model\ResourceModel\Product\Index\Collection\AbstractCollection
     */
    protected $_collection;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_productVisibility;

    /**
     * @var \Magento\Reports\Model\Product\Index\Factory
     */
    protected $_indexFactory;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Catalog\Model\Product\Visibility $productVisibility
     * @param \Magento\Reports\Model\Product\Index\Factory $indexFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Reports\Model\Product\Index\Factory $indexFactory,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $data
        );
        $this->_productVisibility = $productVisibility;
        $this->_indexFactory = $indexFactory;
        $this->_isScopePrivate = true;
    }

    /**
     * Retrieve page size
     *
     * @return int
     */
    public function getPageSize()
    {
        if ($this->hasData('page_size')) {
            return $this->getData('page_size');
        }
        return 5;
    }

    /**
     * Retrieve product ids, that must not be included in collection
     *
     * @return array
     */
    protected function _getProductsToSkip()
    {
        return [];
    }

    /**
     * Public method for retrieve Product Index model
     *
     * @return \Magento\Reports\Model\Product\Index\AbstractIndex
     */
    public function getModel()
    {
        try {
            $model = $this->_indexFactory->get($this->_indexType);
        } catch (\InvalidArgumentException $e) {
            new \Magento\Framework\Exception\LocalizedException(__('Index type is not valid'));
        }

        return $model;
    }

    /**
     * Retrieve Index Product Collection
     *
     * @return \Magento\Reports\Model\ResourceModel\Product\Index\Collection\AbstractCollection
     */
    public function getItemsCollection()
    {
        if ($this->_collection === null) {
            $attributes = $this->_catalogConfig->getProductAttributes();

            $this->_collection = $this->getModel()->getCollection()->addAttributeToSelect($attributes);

            if ($this->getCustomerId()) {
                $this->_collection->setCustomerId($this->getCustomerId());
            }

            $this->_collection->excludeProductIds(
                $this->getModel()->getExcludeProductIds()
            )->addUrlRewrite()->setPageSize(
                $this->getPageSize()
            )->setCurPage(
                1
            );

            /* Price data is added to consider item stock status using price index */
            $this->_collection->addPriceData();

            $ids = $this->getProductIds();
            if (empty($ids)) {
                $this->_collection->addIndexFilter();
            } else {
                $this->_collection->addFilterByIds($ids);
            }
            $this->_collection->setAddedAtOrder()->setVisibility($this->_productVisibility->getVisibleInSiteIds());
        }

        return $this->_collection;
    }

    /**
     * Retrieve count of product index items
     *
     * @return int
     */
    public function getCount()
    {
        if (!$this->getModel()->getCount()) {
            return 0;
        }
        return $this->getItemsCollection()->count();
    }
}
