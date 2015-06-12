<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
     * The name used in the {{trans}} directive
     */
    const TRANS_DIRECTIVE_NAME = 'trans';

    /**
     * The regex to match interior portion of a {{trans "foo"}} translation directive
     */
    const TRANS_DIRECTIVE_REGEX = '/^\s*([\'"])([^\1]*?)(?<!\\\)\1(\s.*)?$/si';

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
    protected $_modifiers = ['nl2br' => ''];

    /**
     * Whether template being filtered is child of another template
     *
     * @var bool
     */
    private $isChildTemplate = false;

    /**
     * Store id
     *
     * @var int
     */
    protected $_storeId = null;

    /**
     * The template model that is using this filter to process its content
     *
     * @var null|\Magento\Email\Model\AbstractTemplate
     */
    protected $_templateModel = null;

    /**
     * @var bool
     */
    protected $_plainTemplateMode = false;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $_assetRepo;

    /**
     * @var \Psr\Log\LoggerInterface
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
     * @var \Magento\Variable\Model\VariableFactory
     */
    protected $_variableFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
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
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Variable\Model\VariableFactory $coreVariableFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
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
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Variable\Model\VariableFactory $coreVariableFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\App\State $appState,
        \Magento\Backend\Model\UrlInterface $backendUrlBuilder,
        $variables = []
    ) {
        $this->_escaper = $escaper;
        $this->_assetRepo = $assetRepo;
        $this->_logger = $logger;
        $this->_scopeConfig = $scopeConfig;
        $this->_modifiers['escape'] = [$this, 'modifierEscape'];
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
     * Sets whether template being filtered is child of another template
     *
     * @param bool $isChildTemplate
     * @return $this
     */
    public function setIsChildTemplate($isChildTemplate)
    {
        $this->isChildTemplate = (bool) $isChildTemplate;
        return $this;
    }

    /**
     * Gets whether template being filtered is child of another template
     *
     * @return bool
     */
    public function getIsChildTemplate()
    {
        return $this->isChildTemplate;
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
     * Sets the template model that is using this filter to process its content
     *
     * @param \Magento\Email\Model\AbstractTemplate $templateModel
     * @return $this
     */
    public function setTemplateModel(\Magento\Email\Model\AbstractTemplate $templateModel)
    {
        $this->_templateModel = $templateModel;
        return $this;
    }

    /**
     * Gets the template model that is using this filter to process its content
     *
     * @return null|\Magento\Email\Model\AbstractTemplate
     */
    public function getTemplateModel()
    {
        return $this->_templateModel;
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
        $skipParams = ['class', 'id', 'output'];
        $blockParameters = $this->_getParameters($construction[2]);
        $block = null;

        if (isset($blockParameters['class'])) {
            $block = $this->_layout->createBlock($blockParameters['class'], null, ['data' => $blockParameters]);
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
        $this->_directiveParams = $this->_getParameters($construction[2]);
        if (!isset($this->_directiveParams['area'])) {
            $this->_directiveParams['area'] = 'frontend';
        }
        if ($this->_directiveParams['area'] != $this->_appState->getAreaCode()) {
            return $this->_appState->emulateAreaCode(
                $this->_directiveParams['area'],
                [$this, 'emulateAreaCallback']
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
        $skipParams = ['handle', 'area'];

        /** @var $layout \Magento\Framework\View\LayoutInterface */
        $layout = $this->_layoutFactory->create(['cacheable' => false]);
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
        $params = $this->_getParameters($construction[2]);
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
        $params = $this->_getParameters($construction[2]);
        return $this->_storeManager->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . $params['url'];
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
        $params = $this->_getParameters($construction[2]);
        if (!isset($params['_query'])) {
            $params['_query'] = [];
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
     * Trans directive for localized strings support
     *
     * Usage:
     *
     *   {{trans "string to translate"}}
     *   {{trans "string to %var" var="$variable"}}
     *
     * The |escape modifier is applied by default, use |raw to override
     *
     * @param string[] $construction
     * @return string
     */
    public function transDirective($construction)
    {
        list($directive, $modifiers) = $this->explodeModifiers($construction[2], 'escape');

        list($text, $params) = $this->getTransParameters($directive);
        if (empty($text)) {
            return '';
        }

        $text = (new \Magento\Framework\Phrase($text, $params))->render();
        return $this->applyModifiers($text, $modifiers);
    }

    /**
     * Return associative array of parameters, using
     * __trans to identify the nameless text argument
     *
     * @param string $value raw parameters
     * @return array always a two-part array in the format [value, [param, ...]]
     */
    protected function getTransParameters($value) {
        if (preg_match(self::TRANS_DIRECTIVE_REGEX, $value, $matches) !== 1) {
            return ['', []];  // malformed directive body; return without breaking list
        }

        $text = stripslashes($matches[2]);

        $params = [];
        if (!empty($matches[3])) {
            $params = $this->_getParameters($matches[3]);
        }

        return [$text, $params];
    }

    /**
     * Var directive with modifiers support
     *
     * The |escape modifier is applied by default, use |raw to override
     *
     * @param string[] $construction
     * @return string
     */
    public function varDirective($construction)
    {
        // just return the escaped value if no template vars exist to process
        if (count($this->_templateVars) == 0) {
            return $construction[0];
        }

        list($directive, $modifiers) = $this->explodeModifiers($construction[2], 'escape');
        return $this->applyModifiers($this->_getVariable($directive, ''), $modifiers);
    }

    /**
     * Explodes modifiers out of a given string value returning
     * the value and modifiers in a two-element array. If none
     * are found, returns an array with null modifier string.
     *
     * Syntax: some text value, etc|modifier string
     *
     * Result: ['some text value, etc', 'modifier string']
     *
     * @param $value
     * @param null $default assumed modifier if none present
     * @return array
     */
    protected function explodeModifiers($value, $default = null)
    {
        $parts = explode('|', $value, 2);
        if (2 === count($parts)) {
            return $parts;
        }
        return [$value, $default];
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
    protected function applyModifiers($value, $modifiers)
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
     * @throws \Magento\Framework\Exception\MailException
     * @return string
     */
    public function protocolDirective($construction)
    {
        $params = $this->_getParameters($construction[2]);
        $store = null;
        if (isset($params['store'])) {
            try {
                $store = $this->_storeManager->getStore($params['store']);
            } catch (\Exception $e) {
                throw new \Magento\Framework\Exception\MailException(
                    __('Requested invalid store "%1"', $params['store'])
                );
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
        $params = $this->_getParameters($construction[2]);
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
        $params = $this->_getParameters($construction[2]);
        if (isset($params['code'])) {
            $variable = $this->_variableFactory->create()->setStoreId(
                $this->getStoreId()
            )->loadByCode(
                $params['code']
            );
            $mode = $this->_plainTemplateMode
                ? \Magento\Variable\Model\Variable::TYPE_TEXT
                : \Magento\Variable\Model\Variable::TYPE_HTML;
            $value = $variable->getValue($mode);
            if ($value) {
                $customVarValue = $value;
            }
        }
        return $customVarValue;
    }

    /**
     * This directive will load and return the contents of a CSS file.
     * Syntax: {{css file=""}}
     *
     * @param string[] $construction
     * @return string
     */
    public function cssDirective($construction)
    {
        $params = $this->_getParameters($construction[2]);
        $file = isset($params['file']) ? $params['file'] : null;
        if ($file) {
            $css = $this->getTemplateModel()
                ->getCssFilesContent($params['file']);

            if (strpos($css, \Magento\Framework\Css\PreProcessor\Adapter\Oyejorge::ERROR_MESSAGE_PREFIX)
                !== false
            ) {
                // Return LESS compilation error wrapped in CSS comment
                return '/*' . PHP_EOL . $css . PHP_EOL . '*/';
            } elseif (!empty($css)) {
                return $css;
            } else {
                // Return CSS comment for debugging purposes
                return sprintf(__('/* Contents of %s could not be loaded or is empty */'), $file);
            }
        }

        // Return CSS comment for debugging purposes
        return __('/* "file" argument must be specified */');
    }

    /**
     * This directive will cause CSS files to be applied inline to the HTML in the email template.
     * This directive does not inline the CSS itself, but adds the files to the parent template model so that the model
     * can handle the inlining at a later point, once all HTML has been assembled.
     * Syntax: {{inlinecss file=""}}
     *
     * @param string[] $construction
     * @return string
     */
    public function inlinecssDirective($construction)
    {
        // If this template is a child of another template, skip processing so that the parent template will process
        // this directive. This is important as CSS inlining must operate on the entire HTML document.
        if ($this->getIsChildTemplate()) {
            return $construction[0];
        }

        $params = $this->_getParameters($construction[2]);
        if (isset($params['file'])) {
            $this->getTemplateModel()
                ->addInlineCssFile($params['file']);
        }
        return '';
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
            if ($this->_appState->getMode() == \Magento\Framework\App\State::MODE_DEVELOPER) {
                $value = sprintf(__('Error filtering template: %s'), $e->getMessage());
            } else {
                $value = '';
            }
            $this->_logger->critical($e);
        }
        return $value;
    }
}
