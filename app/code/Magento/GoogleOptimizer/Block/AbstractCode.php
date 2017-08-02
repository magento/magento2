<?php
/**
 * Google Optimizer Scripts Block
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleOptimizer\Block;

/**
 * Class \Magento\GoogleOptimizer\Block\AbstractCode
 *
 * @since 2.0.0
 */
abstract class AbstractCode extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Entity name in registry
     * @since 2.0.0
     */
    protected $_registryName;

    /**
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $_registry;

    /**
     * @var \Magento\GoogleOptimizer\Helper\Data
     * @since 2.0.0
     */
    protected $_helper;

    /**
     * @var \Magento\GoogleOptimizer\Helper\Code
     * @since 2.0.0
     */
    protected $_codeHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\GoogleOptimizer\Helper\Data $helper
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\GoogleOptimizer\Helper\Code $codeHelper
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\GoogleOptimizer\Helper\Data $helper,
        \Magento\Framework\Registry $registry,
        \Magento\GoogleOptimizer\Helper\Code $codeHelper,
        array $data = []
    ) {
        $this->_helper = $helper;
        $this->_registry = $registry;
        $this->_codeHelper = $codeHelper;
        parent::__construct($context, $data);
    }

    /**
     * Get google experiment code model
     *
     * @return \Magento\GoogleOptimizer\Model\Code
     * @throws \RuntimeException
     * @since 2.0.0
     */
    protected function _getGoogleExperiment()
    {
        return $this->_codeHelper->getCodeObjectByEntity($this->_getEntity());
    }

    /**
     * Render block HTML
     *
     * @return string
     * @since 2.0.0
     */
    protected function _toHtml()
    {
        return parent::_toHtml() . $this->_getScriptCode();
    }

    /**
     * Return script code
     *
     * @return string
     * @since 2.0.0
     */
    protected function _getScriptCode()
    {
        $result = '';

        if ($this->_helper->isGoogleExperimentActive() && $this->_getGoogleExperiment()) {
            $result = $this->_getGoogleExperiment()->getData('experiment_script');
        }
        return $result;
    }

    /**
     * Get entity from registry
     *
     * @return mixed
     * @throws \RuntimeException
     * @since 2.0.0
     */
    protected function _getEntity()
    {
        $entity = $this->_registry->registry($this->_registryName);
        if (!$entity) {
            throw new \RuntimeException('Entity is not found in registry.');
        }
        return $entity;
    }
}
