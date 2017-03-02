<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Model;

use Magento\Store\Model\StoreManagerInterface;

/**
 * Template model
 *
 * @method \Magento\Email\Model\ResourceModel\Template _getResource()
 * @method \Magento\Email\Model\ResourceModel\Template getResource()
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
class Template extends AbstractTemplate implements \Magento\Framework\Mail\TemplateInterface
{
    /**
     * Configuration path for default email templates
     */
    const XML_PATH_SENDING_SET_RETURN_PATH = 'system/smtp/set_return_path';

    const XML_PATH_SENDING_RETURN_PATH_EMAIL = 'system/smtp/return_path_email';

    /**
     * Config path to mail sending setting that shows if email communications are disabled
     * @deprecated
     * @see \Magento\Email\Model\Mail\TransportInterfacePlugin::XML_PATH_SYSTEM_SMTP_DISABLE
     */
    const XML_PATH_SYSTEM_SMTP_DISABLE = 'system/smtp/disable';

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
     * Email filter factory
     *
     * @var \Magento\Email\Model\Template\FilterFactory
     */
    private $filterFactory;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\View\DesignInterface $design
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Store\Model\App\Emulation $appEmulation
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param Template\Config $emailConfig
     * @param TemplateFactory $templateFactory
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param \Magento\Framework\UrlInterface $urlModel
     * @param Template\FilterFactory $filterFactory
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
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Email\Model\Template\Config $emailConfig,
        \Magento\Email\Model\TemplateFactory $templateFactory,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Magento\Framework\UrlInterface $urlModel,
        \Magento\Email\Model\Template\FilterFactory $filterFactory,
        array $data = []
    ) {
        $this->filterFactory = $filterFactory;
        parent::__construct(
            $context,
            $design,
            $registry,
            $appEmulation,
            $storeManager,
            $assetRepo,
            $filesystem,
            $scopeConfig,
            $emailConfig,
            $templateFactory,
            $filterManager,
            $urlModel,
            $data
        );
    }

    /**
     * Initialize email template model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Email\Model\ResourceModel\Template::class);
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
        return $this->getSenderName() && $this->getSenderEmail() && $this->getTemplateSubject();
    }

    /**
     * Getter for template type
     *
     * @return int
     */
    public function getType()
    {
        $templateType = $this->getTemplateType();
        if (null === $templateType && $this->getId()) {
            $templateType = $this->emailConfig->getTemplateType($this->getId());
            $templateType = $templateType == 'html' ? self::TYPE_HTML : self::TYPE_TEXT;
        }
        return $templateType !== null ? $templateType : self::TYPE_HTML;
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

        $variables['this'] = $this;

        $processor->setVariables($variables);

        $this->applyDesignConfig();
        $storeId = $this->getDesignConfig()->getStore();
        try {
            $processedResult = $processor->setStoreId($storeId)->filter(__($this->getTemplateSubject()));
        } catch (\Exception $e) {
            $this->cancelDesignConfig();
            throw new \Magento\Framework\Exception\MailException(__($e->getMessage()), $e);
        }
        $this->cancelDesignConfig();
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
            throw new \Magento\Framework\Exception\MailException(__('Please enter a template name.'));
        }
        if ($this->_getResource()->checkCodeUsage($this)) {
            throw new \Magento\Framework\Exception\MailException(__('Duplicate Of Template Name'));
        }
        parent::beforeSave();
        return $this;
    }

    /**
     * Get processed template
     *
     * @return string
     * @throws \Magento\Framework\Exception\MailException
     */
    public function processTemplate()
    {
        // Support theme fallback for email templates
        $isDesignApplied = $this->applyDesignConfig();

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
        $text = $this->getProcessedTemplate($this->_getVars());

        if ($isDesignApplied) {
            $this->cancelDesignConfig();
        }
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
     * @return \Magento\Email\Model\Template\FilterFactory
     */
    protected function getFilterFactory()
    {
        return $this->filterFactory;
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
