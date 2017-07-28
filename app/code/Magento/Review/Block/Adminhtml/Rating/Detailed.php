<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Block\Adminhtml\Rating;

use Magento\Review\Model\Rating;
use Magento\Review\Model\Rating\Option;
use Magento\Review\Model\ResourceModel\Rating\Collection as RatingCollection;
use Magento\Review\Model\ResourceModel\Rating\Option\Vote\Collection as VoteCollection;

/**
 * Adminhtml detailed rating stars
 * @since 2.0.0
 */
class Detailed extends \Magento\Backend\Block\Template
{
    /**
     * Vote collection
     *
     * @var VoteCollection
     * @since 2.0.0
     */
    protected $_voteCollection = false;

    /**
     * Rating detail template name
     *
     * @var string
     * @since 2.0.0
     */
    protected $_template = 'Magento_Review::rating/detailed.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $_coreRegistry = null;

    /**
     * Rating resource model
     *
     * @var \Magento\Review\Model\ResourceModel\Rating\CollectionFactory
     * @since 2.0.0
     */
    protected $_ratingsFactory;

    /**
     * Rating resource option model
     *
     * @var \Magento\Review\Model\ResourceModel\Rating\Option\Vote\CollectionFactory
     * @since 2.0.0
     */
    protected $_votesFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Review\Model\ResourceModel\Rating\CollectionFactory $ratingsFactory
     * @param \Magento\Review\Model\ResourceModel\Rating\Option\Vote\CollectionFactory $votesFactory
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Review\Model\ResourceModel\Rating\CollectionFactory $ratingsFactory,
        \Magento\Review\Model\ResourceModel\Rating\Option\Vote\CollectionFactory $votesFactory,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_ratingsFactory = $ratingsFactory;
        $this->_votesFactory = $votesFactory;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Initialize review data
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        parent::_construct();

        if ($this->_coreRegistry->registry('review_data')) {
            $this->setReviewId($this->_coreRegistry->registry('review_data')->getReviewId());
        }
    }

    /**
     * Get collection of ratings
     *
     * @return RatingCollection
     * @since 2.0.0
     */
    public function getRating()
    {
        if (!$this->getRatingCollection()) {
            if ($this->_coreRegistry->registry('review_data')) {
                $stores = $this->_coreRegistry->registry('review_data')->getStores();

                $stores = array_diff($stores, [0]);

                $ratingCollection = $this->_ratingsFactory->create()->addEntityFilter(
                    'product'
                )->setStoreFilter(
                    $stores
                )->setActiveFilter(
                    true
                )->setPositionOrder()->load()->addOptionToItems();

                $this->_voteCollection = $this->_votesFactory->create()->setReviewFilter(
                    $this->getReviewId()
                )->addOptionInfo()->load()->addRatingOptions();
            } elseif (!$this->getIsIndependentMode()) {
                $ratingCollection = $this->_ratingsFactory->create()->addEntityFilter(
                    'product'
                )->setStoreFilter(
                    null
                )->setPositionOrder()->load()->addOptionToItems();
            } else {
                $stores = $this->getRequest()->getParam('select_stores') ?: $this->getRequest()->getParam('stores');
                $ratingCollection = $this->_ratingsFactory->create()->addEntityFilter(
                    'product'
                )->setStoreFilter(
                    $stores
                )->setPositionOrder()->load()->addOptionToItems();
                if (intval($this->getRequest()->getParam('id'))) {
                    $this->_voteCollection = $this->_votesFactory->create()->setReviewFilter(
                        intval($this->getRequest()->getParam('id'))
                    )->addOptionInfo()->load()->addRatingOptions();
                }
            }
            $this->setRatingCollection($ratingCollection->getSize() ? $ratingCollection : false);
        }
        return $this->getRatingCollection();
    }

    /**
     * Set independent mode
     *
     * @return $this
     * @since 2.0.0
     */
    public function setIndependentMode()
    {
        $this->setIsIndependentMode(true);
        return $this;
    }

    /**
     * Indicator of whether or not a rating is selected
     *
     * @param Option $option
     * @param \Magento\Review\Model\Rating $rating
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function isSelected($option, $rating)
    {
        if ($this->getIsIndependentMode()) {
            $ratings = $this->getRequest()->getParam('ratings');

            if (isset($ratings[$option->getRatingId()])) {
                return $option->getId() == $ratings[$option->getRatingId()];
            } elseif (!$this->_voteCollection) {
                return false;
            }
        }

        if ($this->_voteCollection) {
            foreach ($this->_voteCollection as $vote) {
                if ($option->getId() == $vote->getOptionId()) {
                    return true;
                }
            }
        }
        return false;
    }
}
