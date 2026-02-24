<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Model\ResourceModel\Order\Creditmemo\Comment;

use Magento\Sales\Api\Data\CreditmemoCommentSearchResultInterface;
use Magento\Sales\Model\ResourceModel\Order\Comment\Collection\AbstractCollection;

/**
 * Flat sales order creditmemo comments collection
 *
 * @api
 * @since 100.0.2
 */
class Collection extends AbstractCollection implements CreditmemoCommentSearchResultInterface
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'sales_order_creditmemo_comment_collection';

    /**
     * @var string
     */
    protected $_eventObject = 'order_creditmemo_comment_collection';

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(
            \Magento\Sales\Model\Order\Creditmemo\Comment::class,
            \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Comment::class
        );
    }

    /**
     * Set creditmemo filter
     *
     * @param int $creditmemoId
     * @return $this
     */
    public function setCreditmemoFilter($creditmemoId)
    {
        return $this->setParentFilter($creditmemoId);
    }
}
