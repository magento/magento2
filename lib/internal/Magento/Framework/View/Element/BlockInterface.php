<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\View\Element;

/**
 * Magento Block
 *
 * Used to present information to user
 *
 * @api
 * @since 100.0.2
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
