<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Block\Html;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\ScopeInterface;

/**
 * Html page notices block
 *
 * @api
 * @since 100.0.2
 */
class Notices extends Template
{
    /**
     * @param Context $context
     * @param array $data
     */
    public function __construct(Context $context, array $data = [])
    {
        parent::__construct($context, $data);
    }

    /**
     * Check if noscript notice should be displayed
     *
     * @return boolean
     */
    public function displayNoscriptNotice()
    {
        return $this->_scopeConfig->getValue(
            'web/browser_capabilities/javascript',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check if no local storage notice should be displayed
     *
     * @return boolean
     */
    public function displayNoLocalStorageNotice()
    {
        return $this->_scopeConfig->getValue(
            'web/browser_capabilities/local_storage',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check if demo store notice should be displayed
     *
     * @return boolean
     */
    public function displayDemoNotice()
    {
        return $this->_scopeConfig->getValue(
            'design/head/demonotice',
            ScopeInterface::SCOPE_STORE
        );
    }
}
