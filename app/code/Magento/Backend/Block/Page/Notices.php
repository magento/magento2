<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Adminhtml header notices block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Backend\Block\Page;

class Notices extends \Magento\Backend\Block\Template
{
    /**
     * Check if noscript notice should be displayed
     *
     * @return boolean
     */
    public function displayNoscriptNotice()
    {
        return $this->_scopeConfig->getValue('web/browser_capabilities/javascript', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Check if demo store notice should be displayed
     *
     * @return boolean
     */
    public function displayDemoNotice()
    {
        return $this->_scopeConfig->getValue('design/head/demonotice', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
