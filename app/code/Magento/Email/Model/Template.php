<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Model;

use Magento\Email\Model\Template\Filter;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filter\Template as FilterTemplate;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Template model
 *
 * Example:
 *
 * // Loading of template
 * \Magento\Email\Model\TemplateFactory $templateFactory
 * $templateFactory->create()->load($this->_scopeConfig->getValue(
 *  'path_to_email_template_id_config',
 *  \Magento\Store\Model\ScopeInterface::SCOPE_STORE
 *  ));
 * $variables = array(
 *    'someObject' => $this->_coreResourceEmailTemplate
 *    'someString' => 'Some string value'
 * );
 * $emailTemplate->send('some@domain.com', 'Name Of User', $variables);
 *
 * @method \Magento\Email\Model\Resource\Template _getResource()
 * @method \Magento\Email\Model\Resource\Template getResource()
 * @method string getTemplateCode()
 * @method \Magento\Email\Model\Template setTemplateCode(string $value)
 * @method string getTemplateText()
 * @method \Magento\Email\Model\Template setTemplateText(string $value)
 * @method string getTemplateStyles()
 * @method \Magento\Email\Model\Template setTemplateStyles(string $value)
 * @method int getTemplateType()
 * @method \Magento\Email\Model\Template setTemplateType(int $value)
 * @method string getTemplateSubject()
 * @method \Magento\Email\Model\Template setTemplateSubject(string $value)
 * @method string getTemplateSenderName()
 * @method \Magento\Email\Model\Template setTemplateSenderName(string $value)
 * @method string getTemplateSenderEmail()
 * @method \Magento\Email\Model\Template setTemplateSenderEmail(string $value)
 * @method string getAddedAt()
 * @method \Magento\Email\Model\Template setAddedAt(string $value)
 * @method string getModifiedAt()
 * @method \Magento\Email\Model\Template setModifiedAt(string $value)
 * @method string getOrigTemplateCode()
 * @method \Magento\Email\Model\Template setOrigTemplateCode(string $value)
 * @method string getOrigTemplateVariables()
 * @method \Magento\Email\Model\Template setOrigTemplateVariables(string $value)
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Template extends \Magento\Email\Model\AbstractTemplate implements \Magento\Framework\Mail\TemplateInterface
{
    /**
     * Configuration path for default email templates
     */
    const XML_PATH_SENDING_SET_RETURN_PATH = 'system/smtp/set_return_path';

    const XML_PATH_SENDING_RETURN_PATH_EMAIL = 'system/smtp/return_path_email';

    /**
     * Config path to mail sending setting that shows if email communications are disabled
     */
    const XML_PATH_SYSTEM_SMTP_DISABLE = 'system/smtp/disable';

    /**
     * Email template filter
     *
     * @var FilterTemplate
     */
    protected $_templateFilter;

    /**
     * Email template preprocessed flag
     *
     * @var bool
     */
    protected $_preprocessFlag = false;

    /**
     * BCC list
     *
     * @var array
     */
    protected $_bcc = [];

    /**
     * Return path
     *
     * @var string
     */
    protected $_returnPath = '';

    /**
     * Reply address
     *
     * @var string
     */
    protected $_replyTo = '';

    /**
     * @var array
     */
    protected $_vars = [];

    /**
     * @var \Exception|null
     */
    protected $_sendingException = null;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $_assetRepo;

    /**
     * @var \Magento\Framework\View\FileSystem
     */
    protected $_viewFileSystem;

    /**
     * Scope config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Email\Model\Template\Config
     */
    private $_emailConfig;

    /**
     * Constructor
     *
     * Email filter factory
     *
     * @var \Magento\Email\Model\Template\FilterFactory
     */
    protected $_emailFilterFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\View\DesignInterface $design
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Store\Model\App\Emulation $appEmulation
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\View\FileSystem $viewFileSystem
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param Template\FilterFactory $emailFilterFactory
     * @param Template\Config $emailConfig
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\View\DesignInterface $design,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\App\Emulation $appEmulation,
        StoreManagerInterface $storeManager,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\View\FileSystem $viewFileSystem,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Email\Model\Template\FilterFactory $emailFilterFactory,
        \Magento\Email\Model\Template\Config $emailConfig,
        array $data = []
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_filesystem = $filesystem;
        $this->_assetRepo = $assetRepo;
        $this->_viewFileSystem = $viewFileSystem;
        $this->_objectManager = $objectManager;
        $this->_emailFilterFactory = $emailFilterFactory;
        $this->_emailConfig = $emailConfig;
        parent::__construct($context, $design, $registry, $appEmulation, $storeManager, $data);
    }

    /**
     * Initialize email template model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Email\Model\Resource\Template');
    }

    /**
     * Get default email logo image
     *
     * @return string
     */
    public function getDefaultEmailLogo()
    {
        return $this->_assetRepo->getUrlWithParams(
            'Magento_Email::logo_email.png',
            ['area' => \Magento\Framework\App\Area::AREA_FRONTEND]
        );
    }

    /**
     * Declare template processing filter
     *
     * @param FilterTemplate $filter
     * @return $this
     */
    public function setTemplateFilter(FilterTemplate $filter)
    {
        $this->_templateFilter = $filter;
        return $this;
    }

    /**
     * Get filter object for template processing log
     *
     * @return Filter
     */
    public function getTemplateFilter()
    {
        if (empty($this->_templateFilter)) {
            $this->_templateFilter = $this->_emailFilterFactory->create();
            $this->_templateFilter->setUseAbsoluteLinks(
                $this->getUseAbsoluteLinks()
            )->setStoreId(
                $this->getDesignConfig()->getStore()
            );
        }
        return $this->_templateFilter;
    }

    /**
     * Load template by code
     *
     * @param string $templateCode
     * @return $this
     */
    public function loadByCode($templateCode)
    {
        $this->addData($this->getResource()->loadByCode($templateCode));
        return $this;
    }

    /**
     * Load default email template
     *
     * @param string $templateId
     * @return $this
     */
    public function loadDefault($templateId)
    {
        $templateFile = $this->_emailConfig->getTemplateFilename($templateId);
        $templateType = $this->_emailConfig->getTemplateType($templateId);
        $templateTypeCode = $templateType == 'html' ? self::TYPE_HTML : self::TYPE_TEXT;
        $this->setTemplateType($templateTypeCode);

        $modulesDirectory = $this->_filesystem->getDirectoryRead(DirectoryList::MODULES);
        $templateText = $modulesDirectory->readFile($modulesDirectory->getRelativePath($templateFile));

        /**
         * trim copyright message for text templates
         */
        if ('html' != $templateType
            && preg_match('/^<!--[\w\W]+?-->/m', $templateText, $matches)
            && strpos($matches[0], 'Copyright') > 0
        ) {
            $templateText = str_replace($matches[0], '', $templateText);
        }

        if (preg_match('/<!--@subject\s*(.*?)\s*@-->/u', $templateText, $matches)) {
            $this->setTemplateSubject($matches[1]);
            $templateText = str_replace($matches[0], '', $templateText);
        }

        if (preg_match('/<!--@vars\s*((?:.)*?)\s*@-->/us', $templateText, $matches)) {
            $this->setData('orig_template_variables', str_replace("\n", '', $matches[1]));
            $templateText = str_replace($matches[0], '', $templateText);
        }

        if (preg_match('/<!--@styles\s*(.*?)\s*@-->/s', $templateText, $matches)) {
            $this->setTemplateStyles($matches[1]);
            $templateText = str_replace($matches[0], '', $templateText);
        }

        /**
         * Remove comment lines and extra spaces
         */
        $templateText = trim(preg_replace('#\{\*.*\*\}#suU', '', $templateText));

        $this->setTemplateText($templateText);
        $this->setId($templateId);

        return $this;
    }

    /**
     * Return template id
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->getTemplateId();
    }

    /**
     * Set id of template
     *
     * @param int $value
     * @return $this
     */
    public function setId($value)
    {
        return $this->setTemplateId($value);
    }

    /**
     * Return true if this template can be used for sending queue as main template
     *
     * @return bool
     */
    public function isValidForSend()
    {
        return !$this->_scopeConfig->isSetFlag(
            'system/smtp/disable',
            ScopeInterface::SCOPE_STORE
        ) && $this->getSenderName() && $this->getSenderEmail() && $this->getTemplateSubject();
    }

    /**
     * Getter for template type
     *
     * @return int|string
     */
    public function getType()
    {
        $templateType = $this->getTemplateType();
        if (null === $templateType && $this->getId()) {
            $templateType = $this->_emailConfig->getTemplateType($this->getId());
            $templateType = $templateType == 'html' ? self::TYPE_HTML : self::TYPE_TEXT;
        }
        return $templateType;
    }

    /**
     * Process email template code
     *
     * @param array $variables
     * @return string
     * @throws \Magento\Framework\Exception\MailException
     */
    public function getProcessedTemplate(array $variables = [])
    {
        $processor = $this->getTemplateFilter()
            ->setUseSessionInUrl(false)->setPlainTemplateMode($this->isPlain())
            ->setIsChildTemplate($this->getIsChildTemplate());

        if (!$this->_preprocessFlag) {
            $variables['this'] = $this;
        }

        if (isset($variables['subscriber'])) {
            $processor->setStoreId($variables['subscriber']->getStoreId());
        }

        $this->_applyDesignConfig();

        // Populate the variables array with store, store info, logo, etc. variables
        $variables = $this->_addEmailVariables($variables, $processor->getStoreId());

        $processor->setIncludeProcessor([$this, 'getInclude'])->setVariables($variables);

        $storeId = $this->getDesignConfig()->getStore();
        try {
            // Filter the template text so that all HTML content will be present
            $result = $processor->setStoreId($storeId)->filter($this->getTemplateText());
            // If the {{inlinecss file=""}} directive was included in the template, grab filename to use for inlining
            $this->setInlineCssFiles($processor->getInlineCssFiles());
            // Now that all HTML has been assembled, run email through CSS inlining process
            $processedResult = $this->getPreparedTemplateText($result);
        } catch (\Exception $e) {
            $this->_cancelDesignConfig();
            throw new \Magento\Framework\Exception\MailException(__($e->getMessage()), $e);
        }
        return $processedResult;
    }

    /**
     * Makes additional text preparations for HTML templates
     *
     * @param string $html
     * @return string
     */
    public function getPreparedTemplateText($html = null)
    {
        if (!$html) {
            $html = $this->getTemplateText();
        }

        if ($this->isPlain()) {
            return $html;
        }

        return $this->_applyInlineCss($html);
    }

    /**
     * Get template code for include directive
     *
     * @param string $template
     * @param array $variables
     * @return string
     */
    public function getInclude($templateId, array $variables)
    {
        // TODO: @Erik Refactor this to load templates that are overridden in the core_email_template module
        $thisClass = __CLASS__;
        $includeTemplate = $this->_objectManager->create($thisClass);
        $includeTemplate->setUseAbsoluteLinks(
            $this->getUseAbsoluteLinks()
        )->setStoreId(
            $this->getDesignConfig()->getStore()
        );

        if (is_numeric($templateId)) {
            $includeTemplate->load($templateId);
        } else {
            $includeTemplate->loadDefault($templateId);
        }

        // Indicate that this is a child template so that when the template is being filtered, directives such as
        // {{inlinecss}} can respond accordingly
        $includeTemplate->setIsChildTemplate(true);

        return $includeTemplate->getProcessedTemplate($variables);
    }

    /**
     * Get exception, generated during send() method
     *
     * @return \Exception|null
     * @codeCoverageIgnore
     */
    public function getSendingException()
    {
        return $this->_sendingException;
    }

    /**
     * Process email subject
     *
     * @param array $variables
     * @return string
     * @throws \Magento\Framework\Exception\MailException
     */
    public function getProcessedTemplateSubject(array $variables)
    {
        $processor = $this->getTemplateFilter();

        if (!$this->_preprocessFlag) {
            $variables['this'] = $this;
        }

        $processor->setVariables($variables);

        $this->_applyDesignConfig();
        $storeId = $this->getDesignConfig()->getStore();
        try {
            $processedResult = $processor->setStoreId($storeId)->filter($this->getTemplateSubject());
        } catch (\Exception $e) {
            $this->_cancelDesignConfig();
            throw new \Magento\Framework\Exception\MailException(__($e->getMessage()), $e);
        }
        $this->_cancelDesignConfig();
        return $processedResult;
    }

    /**
     * Add email BCC
     *
     * @param string|array $bcc
     * @return $this
     * @codeCoverageIgnore
     */
    public function addBcc($bcc)
    {
        $this->_bcc[] = $bcc;
        return $this;
    }

    /**
     * Set Return Path
     *
     * @param string $email
     * @return $this
     * @codeCoverageIgnore
     */
    public function setReturnPath($email)
    {
        $this->_returnPath = $email;
        return $this;
    }

    /**
     * Add Reply-To header
     *
     * @param string $email
     * @return $this
     * @codeCoverageIgnore
     */
    public function setReplyTo($email)
    {
        $this->_replyTo = $email;
        return $this;
    }

    /**
     * Parse variables string into array of variables
     *
     * @param string $variablesString
     * @return array
     */
    protected function _parseVariablesString($variablesString)
    {
        $variables = [];
        if ($variablesString && is_string($variablesString)) {
            $variablesString = str_replace("\n", '', $variablesString);
            $variables = \Zend_Json::decode($variablesString);
        }
        return $variables;
    }

    /**
     * Retrieve option array of variables
     *
     * @param boolean $withGroup if true wrap variable options in group
     * @return array
     */
    public function getVariablesOptionArray($withGroup = false)
    {
        $optionArray = [];
        $variables = $this->_parseVariablesString($this->getData('orig_template_variables'));
        if ($variables) {
            foreach ($variables as $value => $label) {
                $optionArray[] = ['value' => '{{' . $value . '}}', 'label' => __('%1', $label)];
            }
            if ($withGroup) {
                $optionArray = ['label' => __('Template Variables'), 'value' => $optionArray];
            }
        }
        return $optionArray;
    }

    /**
     * Validate email template code
     *
     * @throws \Magento\Framework\Exception\MailException
     * @return $this
     */
    public function beforeSave()
    {
        $code = $this->getTemplateCode();
        if (empty($code)) {
            throw new \Magento\Framework\Exception\MailException(__('The template Name must not be empty.'));
        }
        if ($this->_getResource()->checkCodeUsage($this)) {
            throw new \Magento\Framework\Exception\MailException(__('Duplicate Of Template Name'));
        }
        return parent::beforeSave();
    }

    /**
     * Get processed template
     *
     * @return string
     * @throws \Magento\Framework\Exception\MailException
     */
    public function processTemplate()
    {
        $templateId = $this->getId();
        if (is_numeric($templateId)) {
            $this->load($templateId);
        } else {
            $this->loadDefault($templateId);
        }

        if (!$this->getId()) {
            throw new \Magento\Framework\Exception\MailException(
                __('Invalid transactional email code: %1', $templateId)
            );
        }

        $this->setUseAbsoluteLinks(true);
        $text = $this->getProcessedTemplate($this->_getVars(), true);
        return $text;
    }

    /**
     * Get processed subject
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->getProcessedTemplateSubject($this->_getVars());
    }

    /**
     * Set template variables
     *
     * @param array $vars
     * @return $this
     */
    public function setVars(array $vars)
    {
        $this->_vars = $vars;
        return $this;
    }

    /**
     * Set template options
     *
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options)
    {
        return $this->setDesignConfig($options);
    }

    /**
     * Retrieve template variables
     *
     * @return array
     */
    protected function _getVars()
    {
        return $this->_vars;
    }
}
