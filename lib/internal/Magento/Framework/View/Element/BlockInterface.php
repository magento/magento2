<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\View\Element;

/**
 * Magento Block
 *
 * Used to present information to user
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
