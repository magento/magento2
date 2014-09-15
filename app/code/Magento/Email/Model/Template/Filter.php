<?php
/**
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Email\Model\Template;

/**
 * Core Email Template Filter Model
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Filter extends \Magento\Framework\Filter\Template
{
    /**
     * Use absolute links flag
     *
     * @var bool
     */
    protected $_useAbsoluteLinks = false;

    /**
     * Whether to allow SID in store directive: NO
     *
     * @var bool
     */
    protected $_useSessionInUrl = false;

    /**
     * Modifier Callbacks
     *
     * @var array
     */
    protected $_modifiers = array('nl2br' => '');

    /**
     * Store id
     *
     * @var int
     */
    protected $_storeId = null;

    /**
     * @var bool
     */
    protected $_plainTemplateMode = false;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $_assetRepo;

    /**
     * @var \Magento\Framework\Logger
     */
    protected $_logger;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper = null;

    /**
     * Core store config
     * Variable factory
     *
     * @var \Magento\Core\Model\VariableFactory
     */
    protected $_variableFactory;

    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $_layout;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $_layoutFactory;

    /**
     * Setup callbacks for filters
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * Layout directive params
     *
     * @var array
     */
    protected $_directiveParams;

    /**
     * App state
     *
     * @var \Magento\Framework\App\State
     */
    protected $_appState;

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $backendUrlBuilder;
    
    /**
     * @param \Magento\Framework\Stdlib\String $string
     * @param \Magento\Framework\Logger $logger
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Core\Model\VariableFactory $coreVariableFactory
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Backend\Model\UrlInterface $backendUrlBuilder
     * @param array $variables
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Stdlib\String $string,
        \Magento\Framework\Logger $logger,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Core\Model\VariableFactory $coreVariableFactory,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\App\State $appState,
        \Magento\Backend\Model\UrlInterface $backendUrlBuilder,
        $variables = array()
    ) {
        $this->_escaper = $escaper;
        $this->_assetRepo = $assetRepo;
        $this->_logger = $logger;
        $this->_scopeConfig = $scopeConfig;
        $this->_modifiers['escape'] = array($this, 'modifierEscape');
        $this->_variableFactory = $coreVariableFactory;
        $this->_storeManager = $storeManager;
        $this->_layout = $layout;
        $this->_layoutFactory = $layoutFactory;
        $this->_appState = $appState;
        $this->backendUrlBuilder = $backendUrlBuilder;
        parent::__construct($string, $variables);
    }

    /**
     * Set use absolute links flag
     *
     * @param bool $flag
     * @return $this
     */
    public function setUseAbsoluteLinks($flag)
    {
        $this->_useAbsoluteLinks = $flag;
        return $this;
    }

    /**
     * Setter whether SID is allowed in store directive
     * Doesn't set anything intentionally, since SID is not allowed in any kind of emails
     *
     * @param bool $flag
     * @return $this
     */
    public function setUseSessionInUrl($flag)
    {
        $this->_useSessionInUrl = $flag;
        return $this;
    }

    /**
     * Setter
     *
     * @param bool $plainTemplateMode
     * @return $this
     */
    public function setPlainTemplateMode($plainTemplateMode)
    {
        $this->_plainTemplateMode = (bool)$plainTemplateMode;
        return $this;
    }

    /**
     * Setter
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;
        return $this;
    }

    /**
     * Getter. If $_storeId is null, return design store id.
     *
     * @return integer
     */
    public function getStoreId()
    {
        if (null === $this->_storeId) {
            $this->_storeId = $this->_storeManager->getStore()->getId();
        }
        return $this->_storeId;
    }

    /**
     * Retrieve Block html directive
     *
     * @param array $construction
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function blockDirective($construction)
    {
        $skipParams = array('class', 'id', 'output');
        $blockParameters = $this->_getIncludeParameters($construction[2]);
        $block = null;

        if (isset($blockParameters['class'])) {
            $block = $this->_layout->createBlock($blockParameters['class'], null, array('data' => $blockParameters));
        } elseif (isset($blockParameters['id'])) {
            $block = $this->_layout->createBlock('Magento\Cms\Block\Block');
            if ($block) {
                $block->setBlockId($blockParameters['id']);
            }
        }

        if (!$block) {
            return '';
        }

        $block->setBlockParams($blockParameters);
        foreach ($blockParameters as $k => $v) {
            if (in_array($k, $skipParams)) {
                continue;
            }
            $block->setDataUsingMethod($k, $v);
        }


        if (isset($blockParameters['output'])) {
            $method = $blockParameters['output'];
        }
        if (!isset($method)
            || !is_string($method)
            || !method_exists($block, $method)
            || !is_callable([$block, $method])
        ) {
            $method = 'toHtml';
        }
        return $block->{$method}();
    }

    /**
     * Retrieve layout html directive
     *
     * @param string[] $construction
     * @return string
     */
    public function layoutDirective($construction)
    {
        $this->_directiveParams = $this->_getIncludeParameters($construction[2]);
        if (!isset($this->_directiveParams['area'])) {
            $this->_directiveParams['area'] = 'frontend';
        }
        if ($this->_directiveParams['area'] != $this->_appState->getAreaCode()) {
            return $this->_appState->emulateAreaCode(
                $this->_directiveParams['area'],
                array($this, 'emulateAreaCallback')
            );
        } else {
            return $this->emulateAreaCallback();
        }
    }

    /**
     * Retrieve layout html directive callback
     *
     * @return string
     */
    public function emulateAreaCallback()
    {
        $skipParams = array('handle', 'area');

        /** @var $layout \Magento\Framework\View\LayoutInterface */
        $layout = $this->_layoutFactory->create(array('cacheable' => false));
        $layout->getUpdate()->addHandle($this->_directiveParams['handle'])->load();

        $layout->generateXml();
        $layout->generateElements();

        $rootBlock = false;
        foreach ($layout->getAllBlocks() as $block) {
            /* @var $block \Magento\Framework\View\Element\AbstractBlock */
            if (!$block->getParentBlock() && !$rootBlock) {
                $rootBlock = $block;
            }
            foreach ($this->_directiveParams as $k => $v) {
                if (in_array($k, $skipParams)) {
                    continue;
                }
                $block->setDataUsingMethod($k, $v);
            }
        }

        /**
         * Add root block to output
         */
        if ($rootBlock) {
            $layout->addOutputElement($rootBlock->getNameInLayout());
        }

        $result = $layout->getOutput();
        $layout->__destruct();
        // To overcome bug with SimpleXML memory leak (https://bugs.php.net/bug.php?id=62468)
        return $result;
    }

    /**
     * Retrieve block parameters
     *
     * @param mixed $value
     * @return array
     */
    protected function _getBlockParameters($value)
    {
        $tokenizer = new \Magento\Framework\Filter\Template\Tokenizer\Parameter();
        $tokenizer->setString($value);

        return $tokenizer->tokenize();
    }

    /**
     * Retrieve View URL directive
     *
     * @param string[] $construction
     * @return string
     */
    public function viewDirective($construction)
    {
        $params = $this->_getIncludeParameters($construction[2]);
        $url = $this->_assetRepo->getUrlWithParams($params['url'], $params);
        return $url;
    }

    /**
     * Retrieve media file URL directive
     *
     * @param string[] $construction
     * @return string
     */
    public function mediaDirective($construction)
    {
        $params = $this->_getIncludeParameters($construction[2]);
        return $this->_storeManager->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA). $params['url'];
    }

    /**
     * Retrieve store URL directive
     * Support url and direct_url properties
     *
     * @param string[] $construction
     * @return string
     */
    public function storeDirective($construction)
    {
        $params = $this->_getIncludeParameters($construction[2]);
        if (!isset($params['_query'])) {
            $params['_query'] = array();
        }
        foreach ($params as $k => $v) {
            if (strpos($k, '_query_') === 0) {
                $params['_query'][substr($k, 7)] = $v;
                unset($params[$k]);
            }
        }
        $params['_absolute'] = $this->_useAbsoluteLinks;

        if ($this->_useSessionInUrl === false) {
            $params['_nosid'] = true;
        }

        if (isset($params['direct_url'])) {
            $path = '';
            $params['_direct'] = $params['direct_url'];
            unset($params['direct_url']);
        } else {
            $path = isset($params['url']) ? $params['url'] : '';
            unset($params['url']);
        }

        return $this->getUrl($path, $params);
    }

    /**
     * @param string $path
     * @param array $params
     * @return string
     */
    protected function getUrl($path, $params)
    {
        $isBackendStore = \Magento\Store\Model\Store::DEFAULT_STORE_ID === $this->getStoreId()
            || \Magento\Store\Model\Store::ADMIN_CODE === $this->getStoreId();

        return $isBackendStore
            ? $this->backendUrlBuilder->getUrl($path, $params)
            : $this->_storeManager->getStore($this->getStoreId())->getUrl($path, $params);
    }

    /**
     * Directive for converting special characters to HTML entities
     * Supported options:
     *     allowed_tags - Comma separated html tags that have not to be converted
     *
     * @param string[] $construction
     * @return string
     */
    public function escapehtmlDirective($construction)
    {
        $params = $this->_getIncludeParameters($construction[2]);
        if (!isset($params['var'])) {
            return '';
        }

        $allowedTags = null;
        if (isset($params['allowed_tags'])) {
            $allowedTags = preg_split('/\s*\,\s*/', $params['allowed_tags'], 0, PREG_SPLIT_NO_EMPTY);
        }

        return $this->_escaper->escapeHtml($params['var'], $allowedTags);
    }

    /**
     * Var directive with modifiers support
     *
     * @param string[] $construction
     * @return string
     */
    public function varDirective($construction)
    {
        if (count($this->_templateVars) == 0) {
            // If template preprocessing
            return $construction[0];
        }

        $parts = explode('|', $construction[2], 2);
        if (2 === count($parts)) {
            list($variableName, $modifiersString) = $parts;
            return $this->_amplifyModifiers($this->_getVariable($variableName, ''), $modifiersString);
        }
        return $this->_getVariable($construction[2], '');
    }

    /**
     * Apply modifiers one by one, with specified params
     *
     * Modifier syntax: modifier1[:param1:param2:...][|modifier2:...]
     *
     * @param string $value
     * @param string $modifiers
     * @return string
     */
    protected function _amplifyModifiers($value, $modifiers)
    {
        foreach (explode('|', $modifiers) as $part) {
            if (empty($part)) {
                continue;
            }
            $params = explode(':', $part);
            $modifier = array_shift($params);
            if (isset($this->_modifiers[$modifier])) {
                $callback = $this->_modifiers[$modifier];
                if (!$callback) {
                    $callback = $modifier;
                }
                array_unshift($params, $value);
                $value = call_user_func_array($callback, $params);
            }
        }
        return $value;
    }

    /**
     * Escape specified string
     *
     * @param string $value
     * @param string $type
     * @return string
     */
    public function modifierEscape($value, $type = 'html')
    {
        switch ($type) {
            case 'html':
                return htmlspecialchars($value, ENT_QUOTES);

            case 'htmlentities':
                return htmlentities($value, ENT_QUOTES);

            case 'url':
                return rawurlencode($value);
        }
        return $value;
    }

    /**
     * HTTP Protocol directive
     *
     * Using:
     * {{protocol}} - current protocol http or https
     * {{protocol url="www.domain.com/"}} domain URL with current protocol
     * {{protocol http="http://url" https="https://url"}
     * also allow additional parameter "store"
     *
     * @param string[] $construction
     * @throws \Magento\Framework\Mail\Exception
     * @return string
     */
    public function protocolDirective($construction)
    {
        $params = $this->_getIncludeParameters($construction[2]);
        $store = null;
        if (isset($params['store'])) {
            try {
                $store = $this->_storeManager->getStore($params['store']);
            } catch (\Exception $e) {
                throw new \Magento\Framework\Mail\Exception(__('Requested invalid store "%1"', $params['store']));
            }
        }
        $isSecure = $this->_storeManager->getStore($store)->isCurrentlySecure();
        $protocol = $isSecure ? 'https' : 'http';
        if (isset($params['url'])) {
            return $protocol . '://' . $params['url'];
        } elseif (isset($params['http']) && isset($params['https'])) {
            if ($isSecure) {
                return $params['https'];
            }
            return $params['http'];
        }

        return $protocol;
    }

    /**
     * Store config directive
     *
     * @param string[] $construction
     * @return string
     */
    public function configDirective($construction)
    {
        $configValue = '';
        $params = $this->_getIncludeParameters($construction[2]);
        $storeId = $this->getStoreId();
        if (isset($params['path'])) {
            $configValue = $this->_scopeConfig->getValue(
                $params['path'],
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
        return $configValue;
    }

    /**
     * Custom Variable directive
     *
     * @param string[] $construction
     * @return string
     */
    public function customvarDirective($construction)
    {
        $customVarValue = '';
        $params = $this->_getIncludeParameters($construction[2]);
        if (isset($params['code'])) {
            $variable = $this->_variableFactory->create()->setStoreId(
                $this->getStoreId()
            )->loadByCode(
                $params['code']
            );
            $mode = $this->_plainTemplateMode
                ? \Magento\Core\Model\Variable::TYPE_TEXT
                : \Magento\Core\Model\Variable::TYPE_HTML;
            $value = $variable->getValue($mode);
            if ($value) {
                $customVarValue = $value;
            }
        }
        return $customVarValue;
    }

    /**
     * Filter the string as template.
     * Rewrited for logging exceptions
     *
     * @param string $value
     * @return string
     * @SuppressWarnings(PHPMD.ConstructorWithNameAsEnclosingClass)
     */
    public function filter($value)
    {
        try {
            $value = parent::filter($value);
        } catch (\Exception $e) {
            $value = '';
            $this->_logger->logException($e);
        }
        return $value;
    }
}
