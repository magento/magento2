<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Block\Html;

/**
 * Html page notices block
 *
 * @api
 * @since 2.0.0
 */
class Notices extends \Magento\Framework\View\Element\Template
{
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\View\Element\Template\Context $context, array $data = [])
    {
        parent::__construct($context, $data);
    }

    /**
     * Check if noscript notice should be displayed
     *
     * @return boolean
     * @since 2.0.0
     */
    public function displayNoscriptNotice()
    {
        return $this->_scopeConfig->getValue(
            'web/browser_capabilities/javascript',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check if no local storage notice should be displayed
     *
     * @return boolean
     * @since 2.0.0
     */
    public function displayNoLocalStorageNotice()
    {
        return $this->_scopeConfig->getValue(
            'web/browser_capabilities/local_storage',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check if demo store notice should be displayed
     *
     * @return boolean
     * @since 2.0.0
     */
    public function displayDemoNotice()
    {
        return $this->_scopeConfig->getValue(
            'design/head/demonotice',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
