<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element;

/**
 * Magento Block
 *
 * Used to present information to user
 *
 * @api
 */
interface BlockInterface
{
    /**
     * Produce and return block's html output
     *
     * @return string
     */
    public function toHtml();
}
