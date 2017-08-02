<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block;

/**
 * Standard admin block. Adds admin-specific behavior and event.
 * Should be used when you declare a block in admin layout handle.
 *
 * Avoid extending this class if possible.
 *
 * If you need custom presentation logic in your blocks, use this class as block, and declare
 * custom view models in block arguments in layout handle file.
 *
 * Example:
 * <block name="my.block" class="Magento\Backend\Block\Template" template="My_Module::template.phtml" >
 *      <arguments>
 *          <argument name="viewModel" xsi:type="object">My\Module\ViewModel\Custom</argument>
 *      </arguments>
 * </block>
 *
 * @api
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @since 2.0.0
 */
class Template extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\AuthorizationInterface
     * @since 2.0.0
     */
    protected $_authorization;

    /**
     * @var \Magento\Framework\Math\Random
     * @since 2.0.0
     */
    protected $mathRandom;

    /**
     * @var \Magento\Backend\Model\Session
     * @since 2.0.0
     */
    protected $_backendSession;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     * @since 2.0.0
     */
    protected $formKey;

    /**
     * @var \Magento\Framework\Code\NameBuilder
     * @since 2.0.0
     */
    protected $nameBuilder;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    /**
     * Check whether or not the module output is enabled.
     *
     * Because many module blocks belong to Backend module,
     * the feature "Disable module output" doesn't cover Admin area.
     *
     * @param string $moduleName Full module name
     * @return boolean
     * @deprecated 2.2.0 Magento does not support custom disabling/enabling module output since 2.2.0 version
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function isOutputEnabled($moduleName = null)
    {
        return true;
    }

    /**
     * Make this public so that templates can use it properly with template engine
     *
     * @return \Magento\Framework\AuthorizationInterface
     * @since 2.0.0
     */
    public function getAuthorization()
    {
        return $this->_authorization;
    }

    /**
     * Prepare html output
     *
     * @return string
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getToolbar()
    {
        return $this->getLayout()->getBlock('page.actions.toolbar');
    }
}
