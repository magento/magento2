<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

namespace Magento\Review\Helper\Action;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory;

/**
 * Action pager helper for iterating over search results
 *
 * @api
 * @since 100.0.2
 */
class Pager extends AbstractHelper
{
    /**
     * Review collection factory
     *
     * @var CollectionFactory
     */
    protected $reviewCollectionFactory;

    /**
     * Pager constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param CollectionFactory $reviewCollectionFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        CollectionFactory $reviewCollectionFactory
    ) {
        parent::__construct($context);
        $this->reviewCollectionFactory = $reviewCollectionFactory;
    }

    /**
     * Get the next review id.
     *
     * @param int $id
     * @return int|false 
     */
    public function getNextItemId($id)
    {
        return $this->getRelativeReviewId($id, 'gt', 'ASC');
    }

    /**
     * Get the previous review id.
     *
     * @param int $id
     * @return int|false
     */
    public function getPreviousItemId($id)
    {
        return $this->getRelativeReviewId($id, 'lt', 'DESC');
    }

    /**
     * Get the review id based on comparison and order.
     *
     * @param int $id 
     * @param string $operator 
     * @param string $order 
     * @return int|false
     */
    private function getRelativeReviewId($id, $operator, $order)
    {
        $collection = $this->reviewCollectionFactory->create();
        $collection->addFieldToFilter('main_table.review_id', [$operator => $id])
            ->setOrder('main_table.review_id', $order)
            ->setPageSize(1)
            ->setCurPage(1);

        $item = $collection->getFirstItem();
        return $item->getId() ? (int)$item->getId() : false;
    }
}
