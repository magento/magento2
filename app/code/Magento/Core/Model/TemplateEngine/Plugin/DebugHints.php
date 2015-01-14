<?php
/**
 * Plugin for the template engine factory that makes a decision of whether to activate debugging hints or not
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Core\Model\TemplateEngine\Plugin;

class DebugHints
{
    /**#@+
     * XPath of configuration of the debugging hints
     */
    const XML_PATH_DEBUG_TEMPLATE_HINTS = 'dev/debug/template_hints';

    const XML_PATH_DEBUG_TEMPLATE_HINTS_BLOCKS = 'dev/debug/template_hints_blocks';

    /**#@-*/

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $_objectManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $_scopeConfig;

    /**
     * @var \Magento\Core\Helper\Data
     */
    private $_coreData;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Core\Helper\Data $coreData
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Core\Helper\Data $coreData
    ) {
        $this->_objectManager = $objectManager;
        $this->_scopeConfig = $scopeConfig;
        $this->_coreData = $coreData;
    }

    /**
     * Wrap template engine instance with the debugging hints decorator, depending of the store configuration
     *
     * @param \Magento\Framework\View\TemplateEngineFactory $subject
     * @param \Magento\Framework\View\TemplateEngineInterface $invocationResult
     *
     * @return \Magento\Framework\View\TemplateEngineInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCreate(
        \Magento\Framework\View\TemplateEngineFactory $subject,
        \Magento\Framework\View\TemplateEngineInterface $invocationResult
    ) {
        if ($this->_scopeConfig->getValue(self::XML_PATH_DEBUG_TEMPLATE_HINTS, \Magento\Store\Model\ScopeInterface::SCOPE_STORE) && $this->_coreData->isDevAllowed()) {
            $showBlockHints = $this->_scopeConfig->getValue(self::XML_PATH_DEBUG_TEMPLATE_HINTS_BLOCKS, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            return $this->_objectManager->create(
                'Magento\Core\Model\TemplateEngine\Decorator\DebugHints',
                ['subject' => $invocationResult, 'showBlockHints' => $showBlockHints]
            );
        }
        return $invocationResult;
    }
}
