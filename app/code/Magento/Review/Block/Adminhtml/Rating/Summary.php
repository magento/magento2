<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Block\Adminhtml\Rating;

use Magento\Review\Model\ResourceModel\Rating\Collection as RatingCollection;

/**
 * Adminhtml summary rating stars
 * @since 2.0.0
 */
class Summary extends \Magento\Backend\Block\Template
{
    /**
     * Rating summary template name
     *
     * @var string
     * @since 2.0.0
     */
    protected $_template = 'Magento_Review::rating/stars/summary.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $_coreRegistry = null;

    /**
     * Rating resource option model
     *
     * @var \Magento\Review\Model\ResourceModel\Rating\Option\Vote\CollectionFactory
     * @since 2.0.0
     */
    protected $_votesFactory;

    /**
     * Rating model
     *
     * @var \Magento\Review\Model\RatingFactory
     * @since 2.0.0
     */
    protected $_ratingFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Review\Model\ResourceModel\Rating\Option\Vote\CollectionFactory $votesFactory
     * @param \Magento\Review\Model\RatingFactory $ratingFactory
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Review\Model\ResourceModel\Rating\Option\Vote\CollectionFactory $votesFactory,
        \Magento\Review\Model\RatingFactory $ratingFactory,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_votesFactory = $votesFactory;
        $this->_ratingFactory = $ratingFactory;
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
        if ($this->_coreRegistry->registry('review_data')) {
            $this->setReviewId($this->_coreRegistry->registry('review_data')->getId());
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
            $ratingCollection = $this->_votesFactory->create()->setReviewFilter(
                $this->getReviewId()
            )->addRatingInfo()->load();
            $this->setRatingCollection($ratingCollection->getSize() ? $ratingCollection : false);
        }
        return $this->getRatingCollection();
    }

    /**
     * Get rating summary
     *
     * @return string
     * @since 2.0.0
     */
    public function getRatingSummary()
    {
        if (!$this->getRatingSummaryCache()) {
            $this->setRatingSummaryCache($this->_ratingFactory->create()->getReviewSummary($this->getReviewId()));
        }

        return $this->getRatingSummaryCache();
    }
}
