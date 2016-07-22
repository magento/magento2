<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api\Data;

/**
 * Interface CommentInterface
 *
 * @api
 */
interface CommentInterface
{
    /**
     * Gets the comment for the invoice.
     *
     * @return string Comment.
     */
    public function getComment();

    /**
     * Sets the comment for the invoice.
     *
     * @param string $comment
     * @return $this
     */
    public function setComment($comment);

    /**
     * Gets the is-visible-on-storefront flag value for the invoice.
     *
     * @return int Is-visible-on-storefront flag value.
     */
    public function getIsVisibleOnFront();

    /**
     * Sets the is-visible-on-storefront flag value for the invoice.
     *
     * @param int $isVisibleOnFront
     * @return $this
     */
    public function setIsVisibleOnFront($isVisibleOnFront);
}
