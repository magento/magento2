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
namespace Magento\Review\Model;

/**
 * Review Observer Model
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Observer
{
    /**
     * Review model
     *
     * @var \Magento\Review\Model\ReviewFactory
     */
    protected $_reviewFactory;

    /**
     * Review resource model
     *
     * @var \Magento\Review\Model\Resource\Review
     */
    protected $_resourceReview;

    /**
     * @var \Magento\Review\Model\Resource\Rating
     */
    protected $_resourceRating;

    /**
     * @param ReviewFactory $reviewFactory
     * @param Resource\Review $resourceReview
     * @param Resource\Rating $resourceRating
     */
    public function __construct(
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Review\Model\Resource\Review $resourceReview,
        \Magento\Review\Model\Resource\Rating $resourceRating
    ) {
        $this->_reviewFactory = $reviewFactory;
        $this->_resourceReview = $resourceReview;
        $this->_resourceRating = $resourceRating;
    }

    /**
     * Add review summary info for tagged product collection
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function tagProductCollectionLoadAfter(\Magento\Framework\Event\Observer $observer)
    {
        $collection = $observer->getEvent()->getCollection();
        $this->_reviewFactory->create()->appendSummary($collection);

        return $this;
    }

    /**
     * Cleanup product reviews after product delete
     *
     * @param   \Magento\Framework\Event\Observer $observer
     * @return  $this
     */
    public function processProductAfterDeleteEvent(\Magento\Framework\Event\Observer $observer)
    {
        $eventProduct = $observer->getEvent()->getProduct();
        if ($eventProduct && $eventProduct->getId()) {
            $this->_resourceReview->deleteReviewsByProductId($eventProduct->getId());
            $this->_resourceRating->deleteAggregatedRatingsByProductId($eventProduct->getId());
        }

        return $this;
    }

    /**
     * Append review summary before rendering html
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function catalogBlockProductCollectionBeforeToHtml(\Magento\Framework\Event\Observer $observer)
    {
        $productCollection = $observer->getEvent()->getCollection();
        if ($productCollection instanceof \Magento\Framework\Data\Collection) {
            $productCollection->load();
            $this->_reviewFactory->create()->appendSummary($productCollection);
        }

        return $this;
    }
}
