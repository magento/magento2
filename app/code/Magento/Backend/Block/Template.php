<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block;

/**
 * Backend abstract block
 *
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
class Template extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $_authorization;

    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $mathRandom;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $_backendSession;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $formKey;

    /**
     * @var \Magento\Framework\Code\NameBuilder
     */
    protected $nameBuilder;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Template\Context $context, array $data = [])
    {
        $this->_localeDate = $context->getLocaleDate();
        $this->_authorization = $context->getAuthorization();
        $this->mathRandom = $context->getMathRandom();
        $this->_backendSession = $context->getBackendSession();
        $this->formKey = $context->getFormKey();
        $this->nameBuilder = $context->getNameBuilder();
        parent::__construct($context, $data);
    }

    /**
     * Retrieve Session Form Key
     *
     * @return string
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    /**
     * Check whether or not the module output is enabled
     *
     * Because many module blocks belong to Backend module,
     * the feature "Disable module output" doesn't cover Admin area
     *
     * @param string $moduleName Full module name
     * @return boolean
     */
    public function isOutputEnabled($moduleName = null)
    {
        if ($moduleName === null) {
            $moduleName = $this->getModuleName();
        }

        return !$this->_scopeConfig->isSetFlag(
            'advanced/modules_disable_output/' . $moduleName,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Make this public so that templates can use it properly with template engine
     *
     * @return \Magento\Framework\AuthorizationInterface
     */
    public function getAuthorization()
    {
        return $this->_authorization;
    }

    /**
     * Prepare html output
     *
     * @return string
     */
    protected function _toHtml()
    {
        $this->_eventManager->dispatch('adminhtml_block_html_before', ['block' => $this]);
        return parent::_toHtml();
    }

    /**
     * Return toolbar block instance
     *
     * @return bool|\Magento\Framework\View\Element\BlockInterface
     */
    public function getToolbar()
    {
        return $this->getLayout()->getBlock('page.actions.toolbar');
    }
}
