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
namespace Magento\Review\Block\Customer;

use Magento\Catalog\Model\Product;
use Magento\Review\Model\Resource\Rating\Option\Vote\Collection as VoteCollection;
use Magento\Review\Model\Review;

/**
 * Customer Review detailed view block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class View extends \Magento\Catalog\Block\Product\AbstractProduct
{
    /**
     * Customer view template name
     *
     * @var string
     */
    protected $_template = 'customer/view.phtml';

    /**
     * Catalog product model
     *
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * Review model
     *
     * @var \Magento\Review\Model\ReviewFactory
     */
    protected $_reviewFactory;

    /**
     * Rating option model
     *
     * @var \Magento\Review\Model\Rating\Option\VoteFactory
     */
    protected $_voteFactory;

    /**
     * Rating model
     *
     * @var \Magento\Review\Model\RatingFactory
     */
    protected $_ratingFactory;

    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param \Magento\Review\Model\Rating\Option\VoteFactory $voteFactory
     * @param \Magento\Review\Model\RatingFactory $ratingFactory
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Review\Model\Rating\Option\VoteFactory $voteFactory,
        \Magento\Review\Model\RatingFactory $ratingFactory,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        array $data = array()
    ) {
        $this->_productFactory = $productFactory;
        $this->_reviewFactory = $reviewFactory;
        $this->_voteFactory = $voteFactory;
        $this->_ratingFactory = $ratingFactory;
        $this->currentCustomer = $currentCustomer;

        parent::__construct(
            $context,
            $data
        );
    }

    /**
     * Initialize review id
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setReviewId($this->getRequest()->getParam('id', false));
    }

    /**
     * Get product data
     *
     * @return Product
     */
    public function getProductData()
    {
        if ($this->getReviewId() && !$this->getProductCacheData()) {
            $product = $this->_productFactory->create()->setStoreId(
                $this->_storeManager->getStore()->getId()
            )->load(
                $this->getReviewData()->getEntityPkValue()
            );
            $this->setProductCacheData($product);
        }
        return $this->getProductCacheData();
    }

    /**
     * Get review data
     *
     * @return Review
     */
    public function getReviewData()
    {
        if ($this->getReviewId() && !$this->getReviewCachedData()) {
            $this->setReviewCachedData($this->_reviewFactory->create()->load($this->getReviewId()));
        }
        return $this->getReviewCachedData();
    }

    /**
     * Return review customer url
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('review/customer');
    }

    /**
     * Get review rating collection
     *
     * @return VoteCollection
     */
    public function getRating()
    {
        if (!$this->getRatingCollection()) {
            $ratingCollection = $this->_voteFactory->create()->getResourceCollection()->setReviewFilter(
                $this->getReviewId()
            )->addRatingInfo(
                $this->_storeManager->getStore()->getId()
            )->setStoreFilter(
                $this->_storeManager->getStore()->getId()
            )->load();

            $this->setRatingCollection($ratingCollection->getSize() ? $ratingCollection : false);
        }

        return $this->getRatingCollection();
    }

    /**
     * Get rating summary
     *
     * @return array
     */
    public function getRatingSummary()
    {
        if (!$this->getRatingSummaryCache()) {
            $this->setRatingSummaryCache(
                $this->_ratingFactory->create()->getEntitySummary($this->getProductData()->getId())
            );
        }
        return $this->getRatingSummaryCache();
    }

    /**
     * Get total reviews
     *
     * @return int
     */
    public function getTotalReviews()
    {
        if (!$this->getTotalReviewsCache()) {
            $this->setTotalReviewsCache(
                $this->_reviewFactory->create()->getTotalReviews($this->getProductData()->getId()),
                false,
                $this->_storeManager->getStore()->getId()
            );
        }
        return $this->getTotalReviewsCache();
    }

    /**
     * Get formatted date
     *
     * @param string $date
     * @return string
     */
    public function dateFormat($date)
    {
        return $this->formatDate($date, \Magento\Framework\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_LONG);
    }

    /**
     * Check whether current customer is review owner
     *
     * @return bool
     */
    public function isReviewOwner()
    {
        return ($this->getReviewData()->getCustomerId() == $this->currentCustomer->getCustomerId());
    }


    /**
     * Get product reviews summary
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param bool $templateType
     * @param bool $displayIfNoReviews
     * @return string
     */
    public function getReviewsSummaryHtml(
        \Magento\Catalog\Model\Product $product,
        $templateType = false,
        $displayIfNoReviews = false
    ) {
        if (!$product->getRatingSummary()) {
            $this->_reviewFactory->create()->getEntitySummary($product, $this->_storeManager->getStore()->getId());
        }
        return parent::getReviewsSummaryHtml($product, $templateType, $displayIfNoReviews);
    }
}
