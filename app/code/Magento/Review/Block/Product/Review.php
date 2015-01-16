<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Block\Product;


/**
 * Product Review Tab
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Review extends \Magento\Framework\View\Element\Template
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * Review resource model
     *
     * @var \Magento\Review\Model\Resource\Review\CollectionFactory
     */
    protected $_reviewsColFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Review\Model\Resource\Review\CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Review\Model\Resource\Review\CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_reviewsColFactory = $collectionFactory;
        parent::__construct($context, $data);

        $this->setTabTitle();
    }

    /**
     * Get current product id
     *
     * @return null|int
     */
    public function getProductId()
    {
        $product = $this->_coreRegistry->registry('product');
        return $product ? $product->getId() : null;
    }

    /**
     * Get URL for ajax call
     *
     * @return string
     */
    public function getProductReviewUrl()
    {
        return $this->getUrl('review/product/listAjax', ['id' => $this->getProductId()]);
    }

    /**
     * Set tab title
     *
     * @return void
     */
    public function setTabTitle()
    {
        $title = $this->getCollectionSize()
            ? __('Reviews %1', '<span class="counter">' . $this->getCollectionSize() . '</span>')
            : __('Reviews');
        $this->setTitle($title);
    }

    /**
     * Get size of reviews collection
     *
     * @return int
     */
    public function getCollectionSize()
    {
        $collection = $this->_reviewsColFactory->create()->addStoreFilter(
            $this->_storeManager->getStore()->getId()
        )->addStatusFilter(
            \Magento\Review\Model\Review::STATUS_APPROVED
        )->addEntityFilter(
            'product',
            $this->getProductId()
        );

        return $collection->getSize();
    }
}
