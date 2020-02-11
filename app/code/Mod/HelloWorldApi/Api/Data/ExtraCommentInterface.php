<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Mod\HelloWorldApi\Api\Data;

/**
 * Interface ExtraComment
 * @api
 */
interface ExtraCommentInterface
{
    /**
     * Constants
     */
    const COMMENT_ID = 'comment_id';
    const CUSTOMER_ID = 'customer_id';
    const PRODUCT_SKU = 'product_sku';
    const IS_APPROVED = 'is_approved';
    const EXTRA_COMMENT = 'extra_comment';

    /**
     * Gets comment id.
     *
     * @return int
     */
    public function getCommentId();

    /**
     * Sets comment id.
     *
     * @param int $commentId
     * @return $this
     */
    public function setCommentId(int $commentId);

    /**
     * Gets customer id.
     *
     * @return int
     */
    public function getCustomerId();

    /**
     * Sets customer id.
     *
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId(int $customerId);

    /**
     * Gets product sku.
     *
     * @return string
     */
    public function getProductSku();

    /**
     * Sets product sku.
     *
     * @param string $productSku
     * @return $this
     */
    public function setProductSku(string $productSku);

    /**
     * Gets is approved or not.
     *
     * @return int
     */
    public function getIsApproved();

    /**
     * Sets is approved or not.
     *
     * @param int $isApproved
     * @return $this
     */
    public function setIsApproved(int $isApproved);

    /**
     * Gets extra comment.
     *
     * @return string
     */
    public function getExtraComment();

    /**
     * Sets extra comment.
     *
     * @param string $extraComment
     * @return $this
     */
    public function setExtraComment(string $extraComment);
}
