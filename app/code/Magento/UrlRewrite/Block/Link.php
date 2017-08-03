<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Label & link block
 *
 * @method string getLabel()
 * @method string getItemUrl()
 * @method string getItemName()
 */
namespace Magento\UrlRewrite\Block;

/**
 * Class \Magento\UrlRewrite\Block\Link
 *
 * @since 2.0.0
 */
class Link extends \Magento\Framework\View\Element\AbstractBlock
{
    /**
     * Render output
     *
     * @return string
     * @since 2.0.0
     */
    protected function _toHtml()
    {
        return '<p>' . $this->getLabel() . ' <a href="' . $this->getItemUrl() . '">' . $this->escapeHtml(
            $this->getItemName()
        ) . '</a></p>';
    }
}
