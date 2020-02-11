<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Mod\HelloWorldApi\Model;

use Mod\HelloWorldApi\Api\Data\ExtraCommentInterface;
use Mod\HelloWorldApi\Model\ResourceModel\ExtraComment as ExtraCommentResource;
use Magento\Framework\Model\AbstractModel;

/**
 * Product ExtraComment class.
 */
class ExtraComment extends AbstractModel implements ExtraCommentInterface
{
    /**
     * @var string
     */
    protected $_idFieldName = ExtraCommentInterface::COMMENT_ID;

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(ExtraCommentResource::class);
    }

    /**
     * Get comment id.
     *
     * @return int
     */
    public function getCommentId()
    {
        return $this->getData(ExtraCommentInterface::COMMENT_ID);
    }

    /**
     * Set comment id.
     *
     * @param int $commentId
     * @return $this
     */
    public function setCommentId(int $commentId)
    {
        $this->setData(ExtraCommentInterface::COMMENT_ID, $commentId);
        return $this;
    }

    /**
     * Get customer id.
     *
     * @return int
     */
    public function getCustomerId()
    {
        return $this->getData(ExtraCommentInterface::CUSTOMER_ID);
    }

    /**
     * Set customer id.
     *
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId(int $customerId)
    {
        $this->setData(ExtraCommentInterface::CUSTOMER_ID, $customerId);
        return $this;
    }

    /**
     * Get product sku.
     *
     * @return string
     */
    public function getProductSku()
    {
        return $this->getData(ExtraCommentInterface::PRODUCT_SKU);
    }

    /**
     * Set product sku.
     *
     * @param string $product_sku
     * @return $this
     */
    public function setProductSku(string $product_sku)
    {
        $this->setData(ExtraCommentInterface::PRODUCT_SKU, $product_sku);
        return $this;
    }

    /**
     * Get is approved or not.
     *
     * @return int
     */
    public function getIsApproved()
    {
        return $this->getData(ExtraCommentInterface::IS_APPROVED);
    }

    /**
     * Set is approved or not.
     *
     * @param int $isApproved
     * @return $this
     */
    public function setIsApproved(int $isApproved)
    {
        $this->setData(ExtraCommentInterface::IS_APPROVED, $isApproved);
        return $this;
    }

    /**
     * Get comment.
     *
     * @return string
     */
    public function getExtraComment()
    {
        return $this->getData(ExtraCommentInterface::EXTRA_COMMENT);
    }

    /**
     * Set comment.
     *
     * @param string $extraComment
     * @return $this
     */
    public function setExtraComment(string $extraComment)
    {
        $this->setData(ExtraCommentInterface::EXTRA_COMMENT, $extraComment);
        return $this;
    }
}
