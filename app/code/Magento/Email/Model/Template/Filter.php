<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Email\Model\Template;

use Exception;
use Magento\Backend\Model\Url as BackendModelUrl;
use Magento\Cms\Block\Block;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\State;
use Magento\Framework\Css\PreProcessor\Adapter\CssInliner;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Read;
use Magento\Framework\Filter\Template;
use Magento\Framework\Filter\Template\Tokenizer\Parameter;
use Magento\Framework\Filter\VariableResolverInterface;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\ContentProcessorException;
use Magento\Framework\View\Asset\ContentProcessorInterface;
use Magento\Framework\View\Asset\File\NotFoundException;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Model\Information as StoreInformation;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Variable\Model\Source\Variables;
use Magento\Variable\Model\Variable;
use Magento\Variable\Model\VariableFactory;
use Psr\Log\LoggerInterface;

/**
 * Core Email Template Filter Model
 *
 * @api
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Filter extends Template
{
    /**
     * The name used in the {{trans}} directive
     */
    public const TRANS_DIRECTIVE_NAME = 'trans';

    /**
     * The regex to match interior portion of a {{trans "foo"}} translation directive
     */
    public const TRANS_DIRECTIVE_REGEX = '/^\s*([\'"])([^\1]*?)(?<!\\\)\1(\s.*)?$/si';

    /**
     * @var bool
     */
    protected $_useAbsoluteLinks = false;

    /**
     * @var bool
     * @deprecated SID is not being used as query parameter anymore.
     * @see storeDirective
     */
    protected $_useSessionInUrl = false;

    /**
     * @var array
     * @deprecated 101.0.4 Use the new Directive Processor interfaces
     * @see applyModifiers
     */
    protected $_modifiers = ['nl2br' => ''];

    /**
     * @var bool
     */
    private $isChildTemplate = false;

    /**
     * @var []
     */
    private $inlineCssFiles = [];

    /**
     * @var int
     */
    protected $_storeId;

    /**
     * @var array
     */
    private $designParams = [];

    /**
     * @var bool
     */
    private $plainTemplateMode = false;

    /**
     * @var Repository
     */
    protected $_assetRepo;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var Escaper
     */
    protected $_escaper;

    /**
     * Core store config
     * @var VariableFactory
     */
    protected $_variableFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var LayoutInterface
     */
    protected $_layout;

    /**
     * @var LayoutFactory
     */
    protected $_layoutFactory;

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var array
     */
    protected $_directiveParams;

    /**
     * @var State
     */
    protected $_appState;

    /**
     * @var UrlInterface
     */
    protected $urlModel;

    /**
     * @var CssInliner
     */
    private $cssInliner;

    /**
     * @var Variables
     */
    protected $configVariables;

    /**
     * @var Css\Processor
     */
    private $cssProcessor;

    /**
     * @var Filesystem
     */
    private $pubDirectory;

    /**
     * @var Read
     */
    private $pubDirectoryRead;

    /**
     * @var StoreInformation
     */
    private $storeInformation;

    /**
     * @var StateInterface
     */
    private $inlineTranslationState;

    /**
     * Filter constructor.
     * @param StringUtils $string
     * @param LoggerInterface $logger
     * @param Escaper $escaper
     * @param Repository $assetRepo
     * @param ScopeConfigInterface $scopeConfig
     * @param VariableFactory $coreVariableFactory
     * @param StoreManagerInterface $storeManager
     * @param LayoutInterface $layout
     * @param LayoutFactory $layoutFactory
     * @param State $appState
     * @param UrlInterface $urlModel
     * @param Variables $configVariables
     * @param VariableResolverInterface $variableResolver
     * @param Css\Processor $cssProcessor
     * @param Filesystem $pubDirectory
     * @param CssInliner $cssInliner
     * @param array $variables
     * @param array $directiveProcessors
     * @param StoreInformation|null $storeInformation
     * @param StateInterface|null $inlineTranslationState
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        StringUtils $string,
        LoggerInterface $logger,
        Escaper $escaper,
        Repository $assetRepo,
        ScopeConfigInterface $scopeConfig,
        VariableFactory $coreVariableFactory,
        StoreManagerInterface $storeManager,
        LayoutInterface $layout,
        LayoutFactory $layoutFactory,
        State $appState,
        UrlInterface $urlModel,
        Variables $configVariables,
        VariableResolverInterface $variableResolver,
        Css\Processor $cssProcessor,
        Filesystem $pubDirectory,
        CssInliner $cssInliner,
        $variables = [],
        array $directiveProcessors = [],
        ?StoreInformation $storeInformation = null,
        ?StateInterface $inlineTranslationState = null
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
        $this->urlModel = $urlModel;
        $this->cssInliner = $cssInliner;
        $this->cssProcessor = $cssProcessor;
        $this->pubDirectory = $pubDirectory;
        $this->configVariables = $configVariables;
        $this->storeInformation = $storeInformation ?:
            ObjectManager::getInstance()->get(StoreInformation::class);
        $this->inlineTranslationState = $inlineTranslationState ?:
            ObjectManager::getInstance()->get(StateInterface::class);
        parent::__construct($string, $variables, $directiveProcessors, $variableResolver);
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
     *
     * @param bool $flag
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @deprecated SID query parameter is not used in URLs anymore.
     * @see SessionId's in URL
     */
    public function setUseSessionInUrl($flag)
    {
        // phpcs:disable Magento2.Functions.DiscouragedFunction
        trigger_error('Session ID is not used as URL parameter anymore.', E_USER_DEPRECATED);

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
        $this->plainTemplateMode = (bool)$plainTemplateMode;
        return $this;
    }

    /**
     * Check whether template is plain
     *
     * @return bool
     */
    public function isPlainTemplateMode()
    {
        return $this->plainTemplateMode;
    }

    /**
     * Set whether template being filtered is child of another template
     *
     * @param bool $isChildTemplate
     * @return $this
     */
    public function setIsChildTemplate($isChildTemplate)
    {
        $this->isChildTemplate = (bool)$isChildTemplate;
        return $this;
    }

    /**
     * Get whether template being filtered is child of another template
     *
     * @return bool
     */
    public function isChildTemplate()
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
     * Set design parameters
     *
     * @param array $designParams
     * @return $this
     */
    public function setDesignParams(array $designParams)
    {
        $this->designParams = $designParams;
        return $this;
    }

    /**
     * Sets pub directory
     *
     * @param string $dirType
     * @return void
     */
    private function setPubDirectory($dirType)
    {
        $this->pubDirectoryRead = $this->pubDirectory->getDirectoryRead($dirType);
    }

    /**
     * Get design parameters
     *
     * @return array
     */
    public function getDesignParams()
    {
        return $this->designParams;
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
        $blockParameters = $this->getParameters($construction[2]);

        $block = null;

        if (isset($blockParameters['class'])) {
            $block = $this->_layout->createBlock($blockParameters['class'], null, ['data' => $blockParameters]);
        } elseif (isset($blockParameters['id'])) {
            $block = $this->_layout->createBlock(Block::class);
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
        $this->_directiveParams = $this->getParameters($construction[2]);
        if (!isset($this->_directiveParams['area'])) {
            $this->_directiveParams['area'] = Area::AREA_FRONTEND;
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

        /** @var $layout LayoutInterface */
        $layout = $this->_layoutFactory->create(['cacheable' => false]);
        $layout->getUpdate()->addHandle($this->_directiveParams['handle'])->load();

        $layout->generateXml();
        $layout->generateElements();

        $rootBlock = false;
        foreach ($layout->getAllBlocks() as $block) {
            /* @var $block AbstractBlock */
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
        $tokenizer = new Parameter();
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
        $params = $this->getParameters($construction[2]);
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
        // phpcs:disable Magento2.Functions.DiscouragedFunction
        $params = $this->getParameters(html_entity_decode($construction[2], ENT_QUOTES));
        return $this->_storeManager->getStore()
            ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $params['url'];
    }

    /**
     * Retrieve store URL directive
     *
     * Support url and direct_url properties
     *
     * @param string[] $construction
     * @return string
     */
    public function storeDirective($construction)
    {
        $params = $this->getParameters($construction[2]);
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

        /**
         * Pass extra parameter to distinguish stores urls for property Magento\Framework\Url $cacheUrl
         * in multi-store environment
         */
        if (!$this->urlModel instanceof BackendModelUrl) {
            $this->urlModel->setScope($this->_storeManager->getStore());
        }
        $params['_escape_params'] = $this->_storeManager->getStore()->getCode();

        return $this->urlModel->getUrl($path, $params);
    }

    /**
     * Set current URL model, which will be used for URLs generation.
     *
     * @param UrlInterface $urlModel
     * @return $this
     */
    public function setUrlModel(UrlInterface $urlModel)
    {
        $this->urlModel = $urlModel;
        return $this;
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
        $this->inlineTranslationState->disable();
        $text = __($text, $params)->render();
        $this->inlineTranslationState->enable();
        return $this->applyModifiers($text, $modifiers);
    }

    /**
     * Parses directive construction into a multipart array containing the text value and key/value pairs of parameters
     *
     * @param string $value raw parameters
     * @return array always a two-part array in the format [value, [param, ...]]
     */
    protected function getTransParameters($value)
    {
        if ($value === null || preg_match(self::TRANS_DIRECTIVE_REGEX, $value, $matches) !== 1) {
            return ['', []];  // malformed directive body; return without breaking list
        }
        // phpcs:disable Magento2.Functions.DiscouragedFunction
        $text = stripslashes($matches[2]);

        $params = [];
        if (!empty($matches[3])) {
            $params = $this->getParameters($matches[3]);
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
        if (count($this->templateVars) == 0) {
            return $construction[0];
        }

        list($directive, $modifiers) = $this->explodeModifiers(
            $construction[2] . ($construction['filters'] ?? ''),
            'escape'
        );
        return $this->applyModifiers($this->getVariable($directive, ''), $modifiers);
    }

    /**
     * Explode modifiers out of a given string
     *
     * This will return the value and modifiers in a two-element array. Where no modifiers are present in the passed
     * value an array with a null modifier string will be returned
     *
     * Syntax: some text value, etc|modifier string
     *
     * Result: ['some text value, etc', 'modifier string']
     *
     * @param string $value
     * @param string $default assumed modifier if none present
     * @return array
     * @deprecated 101.0.4 Use the new FilterApplier or Directive Processor interfaces
     * @see Directive Processor Interfaces
     */
    protected function explodeModifiers($value, $default = null)
    {
        $parts = $value !== null ? explode('|', $value, 2) : [];
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
     * @deprecated 101.0.4 Use the new FilterApplier or Directive Processor interfaces
     * @see Directive Processor Interfaces
     */
    protected function applyModifiers($value, $modifiers)
    {
        $modifiersParts = $modifiers !== null ? explode('|', $modifiers) : [];
        foreach ($modifiersParts as $part) {
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
                $value = $callback(...$params);
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
     * @deprecated 101.0.4 Use the new FilterApplier or Directive Processor interfaces
     * @see Directive Processor Interfacees
     */
    public function modifierEscape($value, $type = 'html')
    {
        switch ($type) {
            case 'html':
                return $this->_escaper->escapeHtml($value);

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
     * Usage:
     *
     *     {{protocol}} - current protocol http or https
     *     {{protocol url="www.domain.com/"}} - domain URL with current protocol
     *     {{protocol http="http://url" https="https://url"}}
     *     {{protocol store="1"}} - Optional parameter which gets protocol from provide store based on store ID or code
     *
     * @param string[] $construction
     * @return string
     * @throws MailException
     * @throws NoSuchEntityException
     */
    public function protocolDirective($construction)
    {
        $params = $this->getParameters($construction[2]);

        $store = null;
        if (isset($params['store'])) {
            try {
                $store = $this->_storeManager->getStore($params['store']);
            } catch (Exception $e) {
                throw new MailException(
                    __('Requested invalid store "%1"', $params['store'])
                );
            }
        }

        $isSecure = $this->_storeManager->getStore($store)->isCurrentlySecure();
        $protocol = $isSecure ? 'https' : 'http';
        if (isset($params['url'])) {
            return $protocol . '://' . $params['url'];
        } elseif (isset($params['http']) && isset($params['https'])) {
            $this->validateProtocolDirectiveHttpScheme($params);

            if ($isSecure) {
                return $params['https'];
            }
            return $params['http'];
        }

        return $protocol;
    }

    /**
     * Validate protocol directive HTTP parameters.
     *
     * @param string[] $params
     * @return void
     * @throws MailException
     */
    private function validateProtocolDirectiveHttpScheme(array $params) : void
    {
        $parsed_http = parse_url($params['http']);
        $parsed_https = parse_url($params['https']);

        if (empty($parsed_http)) {
            throw new MailException(
                __('Contents of %1 could not be loaded or is empty', $params['http'])
            );
        } elseif (empty($parsed_https)) {
            throw new MailException(
                __('Contents of %1 could not be loaded or is empty', $params['https'])
            );
        } elseif ($parsed_http['scheme'] !== 'http') {
            throw new MailException(
                __('Contents of %1 could not be loaded or is empty', $params['http'])
            );
        } elseif ($parsed_https['scheme'] !== 'https') {
            throw new MailException(
                __('Contents of %1 could not be loaded or is empty', $params['https'])
            );
        }
    }

    /**
     * Store config directive
     *
     * @param string[] $construction
     * @return string
     * @throws NoSuchEntityException
     */
    public function configDirective($construction)
    {
        $configValue = '';
        $params = $this->getParameters($construction[2]);
        $storeId = $this->getStoreId();
        $store = $this->_storeManager->getStore($storeId);
        $storeInformationObj = $this->storeInformation
            ->getStoreInformationObject($store);
        if (isset($params['path']) && $this->isAvailableConfigVariable($params['path'])) {
            $configValue = $this->_scopeConfig->getValue(
                $params['path'],
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
            if ($params['path'] == $this->storeInformation::XML_PATH_STORE_INFO_COUNTRY_CODE) {
                $configValue = $storeInformationObj->getData('country');
            } elseif ($params['path'] == $this->storeInformation::XML_PATH_STORE_INFO_REGION_CODE) {
                $configValue = $storeInformationObj->getData('region') ?
                    $storeInformationObj->getData('region') :
                    $configValue;
            }
        }
        return $configValue;
    }

    /**
     * Check if given variable is available for directive "Config"
     *
     * @param string $variable
     * @return bool
     */
    private function isAvailableConfigVariable($variable)
    {
        return in_array(
            $variable,
            $this->configVariables->getAvailableVars()
        );
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
        $params = $this->getParameters($construction[2]);
        if (isset($params['code'])) {
            $variable = $this->_variableFactory->create()->setStoreId(
                $this->getStoreId()
            )->loadByCode(
                $params['code']
            );
            $mode = $this->isPlainTemplateMode()
                ? Variable::TYPE_TEXT
                : Variable::TYPE_HTML;
            $value = $variable->getValue($mode);
            if ($value) {
                $customVarValue = $value;
            }
        }
        return $customVarValue;
    }

    /**
     * Load and return the contents of a CSS file
     *
     * Usage:
     *
     *     {{css file="css/filename.css"}} - Load file from theme directory
     *     {{css file="Magento_Sales::css/filename.css"}} - Load file from module directory or module directory in theme
     *
     * @param string[] $construction
     * @return string
     */
    public function cssDirective($construction)
    {
        if ($this->isPlainTemplateMode()) {
            return '';
        }

        $params = $this->getParameters($construction[2]);
        $file = isset($params['file']) ? $params['file'] : null;
        if (!$file) {
            // Return CSS comment for debugging purposes
            return '/* ' . __('"file" parameter must be specified') . ' */';
        }

        try {
            $css = $this->cssProcessor->process($this->getCssFilesContent([$params['file']]));
        } catch (ContentProcessorException $exception) {
            return '/*' . PHP_EOL . $exception->getMessage() . PHP_EOL . '*/';
        }

        if (empty($css)) {
            return '/* ' . __('Contents of the specified CSS file could not be loaded or is empty') . ' */';
        }

        return $css;
    }

    /**
     * Set file to apply as inline CSS
     *
     * This directive will cause CSS files to be applied inline to the HTML in the email template.
     * This directive does not inline the CSS itself, but adds the files to the parent template model so that the model
     * can handle the inlining at a later point, once all HTML has been assembled.
     *
     * Usage:
     *
     *     {{inlinecss file="css/filename.css"}} - Load file from theme directory
     *     {{inlinecss file="Magento_Sales::css/filename.css"}} - Load file from module directory or module
     *                                                            directory in theme
     *
     * @param string[] $construction
     * @return string
     * @throws MailException
     */
    public function inlinecssDirective($construction)
    {
        // Plain text templates shouldn't have CSS applied inline
        if ($this->isPlainTemplateMode()) {
            return '';
        }

        // If this template is a child of another template, skip processing so that the parent template will process
        // this directive. This is important as CSS inlining must operate on the entire HTML document.
        if ($this->isChildTemplate()) {
            return $construction[0];
        }

        $params = $this->getParameters($construction[2]);
        if (!isset($params['file']) || !$params['file']) {
            throw new MailException(
                __('"file" parameter must be specified and must not be empty')
            );
        }

        $this->addInlineCssFile($params['file']);

        // CSS should be applied after entire template has been filtered, so add as after filter callback
        $this->addAfterFilterCallback([$this, 'applyInlineCss']);
        return '';
    }

    /**
     * Add filename of CSS file to inline
     *
     * @param string $file
     * @return $this
     */
    protected function addInlineCssFile($file)
    {
        $this->inlineCssFiles[] = $file;
        return $this;
    }

    /**
     * Get filename of CSS file to inline
     *
     * @return array
     */
    protected function getInlineCssFiles()
    {
        return $this->inlineCssFiles;
    }

    /**
     * Load CSS file from materialized static view directory
     *
     * @param [] $files
     * @return string
     * @throws MailException
     * @throws ContentProcessorException
     */
    public function getCssFilesContent(array $files)
    {
        // Remove duplicate files
        $files = array_unique($files);

        $designParams = $this->getDesignParams();
        if (!count($designParams)) {
            throw new MailException(
                __('Design params must be set before calling this method')
            );
        }
        $css = '';
        try {
            foreach ($files as $file) {
                $asset = $this->_assetRepo->createAsset($file, $designParams);
                $this->setPubDirectory($asset->getContext()->getBaseDirType());
                if ($this->pubDirectoryRead->isExist($asset->getPath())) {
                    $css .= $this->pubDirectoryRead->readFile($asset->getPath());
                } else {
                    $css .= $asset->getContent();
                }
            }
        } catch (NotFoundException $exception) {
            $css = '';
        }

        return $css;
    }

    /**
     * Apply Inline CSS
     *
     * Merge HTML and CSS and return HTML that has CSS styles applied "inline" to the HTML tags. This is necessary
     * in order to support all email clients.
     *
     * @param string $html
     * @return string
     * @throws MailException
     */
    public function applyInlineCss($html)
    {
        try {
            // Check to see if the {{inlinecss file=""}} directive set CSS file(s) to inline and then load those files
            $cssToInline = $this->getCssFilesContent($this->getInlineCssFiles());
        } catch (ContentProcessorException $exception) {
            return $this->getExceptionHtml($html, $exception);
        }

        $cssToInline = $this->cssProcessor->process($cssToInline);

        // Only run Emogrify if HTML and CSS contain content
        if (!$html || !$cssToInline) {
            return $html;
        }

        try {
            // Don't try to compile CSS that has compilation errors
            if (strpos($cssToInline, ContentProcessorInterface::ERROR_MESSAGE_PREFIX) !== false) {
                throw new MailException(__('<pre> %1 </pre>', PHP_EOL . $cssToInline . PHP_EOL));
            }
            $this->cssInliner->setHtml($html);
            $this->cssInliner->setCss($cssToInline);
            // Don't parse inline <style> tags, since existing tag is intentionally for non-inline styles
            $this->cssInliner->disableStyleBlocksParsing();
            return $this->cssInliner->process();
        } catch (Exception $exception) {
            return $this->getExceptionHtml($html, $exception);
        }
    }

    /**
     * Handle css inlining exception, log it, add to the content in developer mode
     *
     * @param string $html
     * @param Exception $exception
     * @return string
     */
    private function getExceptionHtml(string $html, Exception $exception): string
    {
        $this->_logger->error($exception);
        if ($this->_appState->getMode() == \Magento\Framework\App\State::MODE_DEVELOPER) {
            return __('CSS inlining error:') . PHP_EOL . $exception->getMessage()
                . PHP_EOL
                . $html;
        }
        return $html;
    }

    /**
     * Filter the string as template
     *
     * Overrides parent method in order to handle exceptions
     *
     * @param string $value
     * @return string
     * @SuppressWarnings(PHPMD.ConstructorWithNameAsEnclosingClass)
     */
    public function filter($value)
    {
        try {
            $value = parent::filter($value);
        } catch (Exception $e) {
            if ($this->_appState->getMode() == State::MODE_DEVELOPER) {
                $value = sprintf(__('Error filtering template: %s')->render(), $e->getMessage());
            } else {
                $value = (string) __("We're sorry, an error has occurred while generating this content.");
            }
            $this->_logger->critical($e);
        } finally {
            // Since a single instance of this class can be used to filter content multiple times, reset callbacks to
            // prevent callbacks running for unrelated content (e.g., email subject and email body)
            $this->resetAfterFilterCallbacks();
        }
        return $value;
    }
}
