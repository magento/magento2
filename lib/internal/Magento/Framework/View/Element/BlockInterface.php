<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element;

/**
 * Magento Block
 *
 * Used to present information to user
 *
 * @api
 * @since 2.0.0
 */
interface BlockInterface
{
    /**
     * Produce and return block's html output
     *
     * @return string
     * @since 2.0.0
     */
    public function toHtml();
}
