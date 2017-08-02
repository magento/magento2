<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Block\Html;

use Magento\Framework\View\Element\Template;

/**
 * Html page welcome block
 *
 * @api
 * @since 2.0.0
 */
class Welcome extends Template
{
    /**
     * Get block message
     *
     * @return string
     * @since 2.0.0
     */
    protected function _toHtml()
    {
        return $this->_layout->getBlock('header')->getWelcome();
    }
}
