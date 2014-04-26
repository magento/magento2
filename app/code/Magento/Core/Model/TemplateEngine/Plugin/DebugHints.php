<?php
/**
 * Plugin for the template engine factory that makes a decision of whether to activate debugging hints or not
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
     * @var \Magento\Framework\ObjectManager
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
     * @param \Magento\Framework\ObjectManager $objectManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Core\Helper\Data $coreData
     */
    public function __construct(
        \Magento\Framework\ObjectManager $objectManager,
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
                array('subject' => $invocationResult, 'showBlockHints' => $showBlockHints)
            );
        }
        return $invocationResult;
    }
}
