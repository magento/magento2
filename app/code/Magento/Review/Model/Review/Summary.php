<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Model\Review;

/**
 * Review summary
 *
 * @api
 *
 * @codeCoverageIgnore
 * @since 2.0.0
 */
class Summary extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Review\Model\ResourceModel\Review\Summary $resource
     * @param \Magento\Review\Model\ResourceModel\Review\Summary\Collection $resourceCollection
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Review\Model\ResourceModel\Review\Summary $resource,
        \Magento\Review\Model\ResourceModel\Review\Summary\Collection $resourceCollection,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Get entity primary key value
     *
     * @return int
     * @since 2.0.0
     */
    public function getEntityPkValue()
    {
        return $this->_getData('entity_pk_value');
    }

    /**
     * Get rating summary data
     *
     * @return string
     * @since 2.0.0
     */
    public function getRatingSummary()
    {
        return $this->_getData('rating_summary');
    }

    /**
     * Get count of reviews
     *
     * @return int
     * @since 2.0.0
     */
    public function getReviewsCount()
    {
        return $this->_getData('reviews_count');
    }
}
