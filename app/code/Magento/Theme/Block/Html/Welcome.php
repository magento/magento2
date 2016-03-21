<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Block\Html;

/**
 * Html page welcome block
 */
class Welcome extends \Magento\Framework\View\Element\Template
{
    /**
     * Get block message
     *
     * @return string
     */
    protected function _toHtml()
    {
        return $this->_layout->getBlock('header')->getWelcome();
    }
}
