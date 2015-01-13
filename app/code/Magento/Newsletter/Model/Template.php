<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Model;

/**
 * Template model
 *
 * @method \Magento\Newsletter\Model\Resource\Template _getResource()
 * @method \Magento\Newsletter\Model\Resource\Template getResource()
 * @method string getTemplateCode()
 * @method \Magento\Newsletter\Model\Template setTemplateCode(string $value)
 * @method \Magento\Newsletter\Model\Template setTemplateText(string $value)
 * @method \Magento\Newsletter\Model\Template setTemplateTextPreprocessed(string $value)
 * @method string getTemplateStyles()
 * @method \Magento\Newsletter\Model\Template setTemplateStyles(string $value)
 * @method int getTemplateType()
 * @method \Magento\Newsletter\Model\Template setTemplateType(int $value)
 * @method string getTemplateSubject()
 * @method \Magento\Newsletter\Model\Template setTemplateSubject(string $value)
 * @method string getTemplateSenderName()
 * @method \Magento\Newsletter\Model\Template setTemplateSenderName(string $value)
 * @method string getTemplateSenderEmail()
 * @method \Magento\Newsletter\Model\Template setTemplateSenderEmail(string $value)
 * @method int getTemplateActual()
 * @method \Magento\Newsletter\Model\Template setTemplateActual(int $value)
 * @method string getAddedAt()
 * @method \Magento\Newsletter\Model\Template setAddedAt(string $value)
 * @method string getModifiedAt()
 * @method \Magento\Newsletter\Model\Template setModifiedAt(string $value)
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Template extends \Magento\Email\Model\AbstractTemplate
{
    /**
     * Template Text Preprocessed flag
     *
     * @var bool
     */
    protected $_preprocessFlag = false;

    /**
     * Mail object
     *
     * @var \Zend_Mail
     */
    protected $_mail;

    /**
     * Store manager to emulate design
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Http-request, used to determine current store in multi-store mode
     *
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * Filter for newsletter text
     *
     * @var \Magento\Newsletter\Model\Template\Filter
     */
    protected $_templateFilter;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * Template factory
     *
     * @var \Magento\Newsletter\Model\TemplateFactory
     */
    protected $_templateFactory;

    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    protected $_filterManager;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\View\DesignInterface $design
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Core\Model\App\Emulation $appEmulation
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Newsletter\Model\Template\Filter $filter
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Newsletter\Model\TemplateFactory $templateFactory
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\View\DesignInterface $design,
        \Magento\Framework\Registry $registry,
        \Magento\Core\Model\App\Emulation $appEmulation,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Newsletter\Model\Template\Filter $filter,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Newsletter\Model\TemplateFactory $templateFactory,
        \Magento\Framework\Filter\FilterManager $filterManager,
        array $data = []
    ) {
        parent::__construct($context, $design, $registry, $appEmulation, $storeManager, $data);
        $this->_storeManager = $storeManager;
        $this->_request = $request;
        $this->_filter = $filter;
        $this->_scopeConfig = $scopeConfig;
        $this->_templateFactory = $templateFactory;
        $this->_filterManager = $filterManager;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Newsletter\Model\Resource\Template');
    }

    /**
     * Validate Newsletter template
     *
     * @return void
     * @throws \Magento\Framework\Model\Exception
     */
    public function validate()
    {
        $validators = [
            'template_code' => [\Zend_Filter_Input::ALLOW_EMPTY => false],
            'template_type' => 'Int',
            'template_sender_email' => 'EmailAddress',
            'template_sender_name' => [\Zend_Filter_Input::ALLOW_EMPTY => false],
        ];
        $data = [];
        foreach (array_keys($validators) as $validateField) {
            $data[$validateField] = $this->getDataUsingMethod($validateField);
        }

        $validateInput = new \Zend_Filter_Input([], $validators, $data);
        if (!$validateInput->isValid()) {
            $errorMessages = [];
            foreach ($validateInput->getMessages() as $messages) {
                if (is_array($messages)) {
                    foreach ($messages as $message) {
                        $errorMessages[] = $message;
                    }
                } else {
                    $errorMessages[] = $messages;
                }
            }

            throw new \Magento\Framework\Model\Exception(join("\n", $errorMessages));
        }
    }

    /**
     * Processing object before save data
     *
     * @return $this
     */
    public function beforeSave()
    {
        $this->validate();
        return parent::beforeSave();
    }

    /**
     * Load template by code
     *
     * @param string $templateCode
     * @return $this
     */
    public function loadByCode($templateCode)
    {
        $this->_getResource()->loadByCode($this, $templateCode);
        return $this;
    }

    /**
     * Getter for template type
     *
     * @return int|string
     */
    public function getType()
    {
        return $this->getTemplateType();
    }

    /**
     * Check is Preprocessed
     *
     * @return bool
     */
    public function isPreprocessed()
    {
        return strlen($this->getTemplateTextPreprocessed()) > 0;
    }

    /**
     * Check Template Text Preprocessed
     *
     * @return bool
     */
    public function getTemplateTextPreprocessed()
    {
        if ($this->_preprocessFlag) {
            $this->setTemplateTextPreprocessed($this->getProcessedTemplate());
        }

        return $this->getData('template_text_preprocessed');
    }

    /**
     * Retrieve processed template
     *
     * @param array $variables
     * @param bool $usePreprocess
     * @return string
     */
    public function getProcessedTemplate(array $variables = [], $usePreprocess = false)
    {
        if (!$this->_preprocessFlag) {
            $variables['this'] = $this;
        }

        if ($this->_storeManager->hasSingleStore()) {
            $this->_filter->setStoreId($this->_storeManager->getStore()->getId());
        } else {
            $this->_filter->setStoreId($this->_request->getParam('store_id'));
        }

        $this->_filter->setIncludeProcessor([$this, 'getInclude'])->setVariables($variables);

        if ($usePreprocess && $this->isPreprocessed()) {
            return $this->_filter->filter($this->getPreparedTemplateText(true));
        }

        return $this->_filter->filter($this->getPreparedTemplateText());
    }

    /**
     * Makes additional text preparations for HTML templates
     *
     * @param bool $usePreprocess Use Preprocessed text or original text
     * @return string
     */
    public function getPreparedTemplateText($usePreprocess = false)
    {
        $text = $usePreprocess ? $this->getTemplateTextPreprocessed() : $this->getTemplateText();

        if ($this->_preprocessFlag || $this->isPlain() || !$this->getTemplateStyles()) {
            return $text;
        }
        // wrap styles into style tag
        $html = "<style type=\"text/css\">\n%s\n</style>\n%s";
        return sprintf($html, $this->getTemplateStyles(), $text);
    }

    /**
     * Retrieve included template
     *
     * @param string $templateCode
     * @param array $variables
     * @return string
     */
    public function getInclude($templateCode, array $variables)
    {
        /** @var \Magento\Newsletter\Model\Template $template */
        $template = $this->_templateFactory->create();
        $template->loadByCode($templateCode)->getProcessedTemplate($variables);
        return $template;
    }

    /**
     * Retrieve processed template subject
     *
     * @param array $variables
     * @return string
     */
    public function getProcessedTemplateSubject(array $variables)
    {
        if (!$this->_preprocessFlag) {
            $variables['this'] = $this;
        }
        return $this->_filterManager->template($this->getTemplateSubject(), ['variables' => $variables]);
    }

    /**
     * Retrieve template text wrapper
     *
     * @return string
     */
    public function getTemplateText()
    {
        if (!$this->getData('template_text') && !$this->getId()) {
            $this->setData(
                'template_text',
                __(
                    'Follow this link to unsubscribe <!-- This tag is for unsubscribe link  -->' .
                    '<a href="{{var subscriber.getUnsubscriptionLink()}}">{{var subscriber.getUnsubscriptionLink()}}' .
                    '</a>'
                )
            );
        }

        return $this->getData('template_text');
    }

    /**
     * Check if template can be added to newsletter queue
     *
     * @return boolean
     */
    public function isValidForSend()
    {
        return !$this->_scopeConfig->isSetFlag(
            \Magento\Email\Model\Template::XML_PATH_SYSTEM_SMTP_DISABLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) && $this->getTemplateSenderName() && $this->getTemplateSenderEmail() && $this->getTemplateSubject();
    }
}
