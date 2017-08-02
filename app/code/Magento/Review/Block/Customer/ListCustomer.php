<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Block\Customer;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;

/**
 * Customer Reviews list block
 *
 * @api
 * @since 2.0.0
 */
class ListCustomer extends \Magento\Customer\Block\Account\Dashboard
{
    /**
     * Product reviews collection
     *
     * @var \Magento\Review\Model\ResourceModel\Review\Product\Collection
     * @since 2.0.0
     */
    protected $_collection;

    /**
     * Review resource model
     *
     * @var \Magento\Review\Model\ResourceModel\Review\Product\CollectionFactory
     * @since 2.0.0
     */
    protected $_collectionFactory;

    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     * @since 2.0.0
     */
    protected $currentCustomer;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param AccountManagementInterface $customerAccountManagement
     * @param \Magento\Review\Model\ResourceModel\Review\Product\CollectionFactory $collectionFactory
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        CustomerRepositoryInterface $customerRepository,
        AccountManagementInterface $customerAccountManagement,
        \Magento\Review\Model\ResourceModel\Review\Product\CollectionFactory $collectionFactory,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        array $data = []
    ) {
        $this->_collectionFactory = $collectionFactory;
        parent::__construct(
            $context,
            $customerSession,
            $subscriberFactory,
            $customerRepository,
            $customerAccountManagement,
            $data
        );
        $this->currentCustomer = $currentCustomer;
    }

    /**
     * Get html code for toolbar
     *
     * @return string
     * @since 2.0.0
     */
    public function getToolbarHtml()
    {
        return $this->getChildHtml('toolbar');
    }

    /**
     * Initializes toolbar
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     * @since 2.0.0
     */
    protected function _prepareLayout()
    {
        if ($this->getReviews()) {
            $toolbar = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'customer_review_list.toolbar'
            )->setCollection(
                $this->getReviews()
            );

            $this->setChild('toolbar', $toolbar);
        }
        return parent::_prepareLayout();
    }

    /**
     * Get reviews
     *
     * @return bool|\Magento\Review\Model\ResourceModel\Review\Product\Collection
     * @since 2.0.0
     */
    public function getReviews()
    {
        if (!($customerId = $this->currentCustomer->getCustomerId())) {
            return false;
        }
        if (!$this->_collection) {
            $this->_collection = $this->_collectionFactory->create();
            $this->_collection
                ->addStoreFilter($this->_storeManager->getStore()->getId())
                ->addCustomerFilter($customerId)
                ->setDateOrder();
        }
        return $this->_collection;
    }

    /**
     * Get review link
     *
     * @return string
     * @deprecated 2.2.0
     * @since 2.0.0
     */
    public function getReviewLink()
    {
        return $this->getUrl('review/customer/view/');
    }

    /**
     * Get review URL
     *
     * @param \Magento\Review\Model\Review $review
     * @return string
     * @since 2.2.0
     */
    public function getReviewUrl($review)
    {
        return $this->getUrl('review/customer/view', ['id' => $review->getReviewId()]);
    }

    /**
     * Get product link
     *
     * @return string
     * @deprecated 2.2.0
     * @since 2.0.0
     */
    public function getProductLink()
    {
        return $this->getUrl('catalog/product/view/');
    }

    /**
     * Get product URL
     *
     * @param \Magento\Review\Model\Review $review
     * @return string
     * @since 2.2.0
     */
    public function getProductUrl($review)
    {
        return $this->getUrl('catalog/product/view', ['id' => $review->getEntityPkValue()]);
    }

    /**
     * Format date in short format
     *
     * @param string $date
     * @return string
     * @since 2.0.0
     */
    public function dateFormat($date)
    {
        return $this->formatDate($date, \IntlDateFormatter::SHORT);
    }

    /**
     * Add review summary
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     * @since 2.0.0
     */
    protected function _beforeToHtml()
    {
        $reviews = $this->getReviews();
        if ($reviews) {
            $reviews->load()->addReviewSummary();
        }
        return parent::_beforeToHtml();
    }
}
