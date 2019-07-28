<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Block\Product;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\Template;

/**
 * Product Review Tab
 *
 * @api
 * @author     Magento Core Team <core@magentocommerce.com>
 * @since 100.0.2
 */
class Review extends Template implements IdentityInterface
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
     * @var \Magento\Review\Model\ResourceModel\Review\CollectionFactory
     */
    protected $_reviewsColFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Review\Model\ResourceModel\Review\CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Review\Model\ResourceModel\Review\CollectionFactory $collectionFactory,
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
        return $this->getUrl(
            'review/product/listAjax',
            [
                '_secure' => $this->getRequest()->isSecure(),
                'id' => $this->getProductId(),
            ]
        );
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

    /**
     * Return unique ID(s) for each object in system
     *
     * @return array
     */
    public function getIdentities()
    {
        return [\Magento\Review\Model\Review::CACHE_TAG];
    }
}
