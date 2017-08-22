<?php
/**
 * Plugin for the template engine factory that makes a decision of whether to activate debugging hints or not
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Developer\Model\TemplateEngine\Plugin;

use Magento\Developer\Helper\Data as DevHelper;
use Magento\Developer\Model\TemplateEngine\Decorator\DebugHintsFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\TemplateEngineFactory;
use Magento\Framework\View\TemplateEngineInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Request\Http;

/**
 * Class \Magento\Developer\Model\TemplateEngine\Plugin\DebugHints
 *
 */
class DebugHints
{
    /**
     * XPath of configuration of the debug block names
     */
    const XML_PATH_DEBUG_TEMPLATE_HINTS_BLOCKS = 'dev/debug/template_hints_blocks';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var DevHelper
     */
    protected $devHelper;

    /**
     * @var DebugHintsFactory
     */
    protected $debugHintsFactory;

    /**
     * XPath of configuration of the debug hints
     *
     * Allowed values:
     *     dev/debug/template_hints_storefront
     *     dev/debug/template_hints_admin
     *
     * @var string
     */
    protected $debugHintsPath;

    /**
     * XPath of configuration of the debug hints show with parameter
     *
     *     dev/debug/template_hints_storefront_show_with_parameter
     *
     * @var string
     */
    private $debugHintsWithParam;

    /**
     * XPath of configuration of the debug hints URL parameter
     *
     *     dev/debug/template_hints_parameter_value
     *
     * @var string
     */
    private $debugHintsParameter;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param DevHelper $devHelper
     * @param DebugHintsFactory $debugHintsFactory
     * @param string $debugHintsPath
     * @param Http $http
     * @param string $debugHintsWithParam
     * @param string $debugHintsParameter
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        DevHelper $devHelper,
        DebugHintsFactory $debugHintsFactory,
        $debugHintsPath,
        Http $http = null,
        $debugHintsWithParam = null,
        $debugHintsParameter = null
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->devHelper = $devHelper;
        $this->debugHintsFactory = $debugHintsFactory;
        $this->debugHintsPath = $debugHintsPath;
        $this->http = $http ?: \Magento\Framework\App\ObjectManager::getInstance()->get(
            \Magento\Framework\App\Request\Http::class
        );
        $this->debugHintsWithParam = $debugHintsWithParam;
        $this->debugHintsParameter = $debugHintsParameter;
    }

    /**
     * Wrap template engine instance with the debugging hints decorator, depending of the store configuration
     *
     * @param TemplateEngineFactory $subject
     * @param TemplateEngineInterface $invocationResult
     *
     * @return TemplateEngineInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCreate(
        TemplateEngineFactory $subject,
        TemplateEngineInterface $invocationResult
    ) {
        $storeCode = $this->storeManager->getStore()->getCode();
        if ($this->scopeConfig->getValue($this->debugHintsPath, ScopeInterface::SCOPE_STORE, $storeCode)
            && $this->devHelper->isDevAllowed()) {
            $debugHintsWithParam = $this->scopeConfig->getValue(
                $this->debugHintsWithParam,
                ScopeInterface::SCOPE_STORE,
                $storeCode
            );
            $debugHintsParameter = $this->scopeConfig->getValue(
                $this->debugHintsParameter,
                ScopeInterface::SCOPE_STORE,
                $storeCode
            );
            $debugHintsParameterInUrl = $this->http->getParam('templatehints');

            $showHints = false;
            if (!$debugHintsWithParam) {
                $showHints = true;
            }
            if ($debugHintsWithParam && $debugHintsParameter == $debugHintsParameterInUrl) {
                $showHints = true;
            }

            if ($showHints) {
                $showBlockHints = $this->scopeConfig->getValue(
                    self::XML_PATH_DEBUG_TEMPLATE_HINTS_BLOCKS,
                    ScopeInterface::SCOPE_STORE,
                    $storeCode
                );
                return $this->debugHintsFactory->create([
                    'subject' => $invocationResult,
                    'showBlockHints' => $showBlockHints,
                ]);
            }
        }
        return $invocationResult;
    }
}
