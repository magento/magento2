<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/**
 * Label & link block
 *
 * @method string getLabel()
 * @method string getItemUrl()
 * @method string getItemName()
 */
namespace Magento\UrlRewrite\Block;

class Link extends \Magento\Framework\View\Element\AbstractBlock
{
    /**
     * Render output
     *
     * @return string
     */
    protected function _toHtml()
    {
        return '<p>' . $this->getLabel() . ' <a href="' . $this->getItemUrl() . '">' . $this->escapeHtml(
            $this->getItemName()
        ) . '</a></p>';
    }
}
