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
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * Constructor
     *
     * Filter factory
     *
     * @var \Magento\Newsletter\Model\Template\FilterFactory
     */
    protected $_filterFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\View\DesignInterface $design
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Store\Model\App\Emulation $appEmulation
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Email\Model\Template\Config $emailConfig
     * @param \Magento\Newsletter\Model\TemplateFactory $templateFactory
     * @param \Magento\Newsletter\Model\Template\FilterFactory $filterFactory,
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\View\DesignInterface $design,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\App\Emulation $appEmulation,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Email\Model\Template\Config $emailConfig,
        \Magento\Newsletter\Model\TemplateFactory $templateFactory,
        \Magento\Newsletter\Model\Template\FilterFactory $filterFactory,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $design,
            $registry,
            $appEmulation,
            $storeManager,
            $assetRepo,
            $filesystem,
            $scopeConfig,
            $objectManager,
            $emailConfig,
            $data
        );
        $this->_storeManager = $storeManager;
        $this->_request = $request;
        $this->_templateFactory = $templateFactory;
        $this->_filterFactory = $filterFactory;
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
     * @throws \Magento\Framework\Exception\LocalizedException
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

            throw new \Magento\Framework\Exception\LocalizedException(__(join("\n", $errorMessages)));
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
     * Declare template processing filter
     *
     * @param \Magento\Newsletter\Model\Template\Filter $filter
     * @return $this
     */
    public function setTemplateFilter(Template\Filter $filter)
    {
        $this->_templateFilter = $filter;
        return $this;
    }

    /**
     * Get filter object for template processing
     *
     * @return \Magento\Newsletter\Model\Template\Filter
     */
    public function getTemplateFilter()
    {
        if (empty($this->_templateFilter)) {
            $this->_templateFilter = $this->_filterFactory->create();
            $this->_templateFilter->setUseAbsoluteLinks(
                $this->getUseAbsoluteLinks()
            )->setStoreId(
                $this->getDesignConfig()->getStore()
            );
        }
        return $this->_templateFilter;
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
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
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
        $this->setUseAbsoluteLinks(true);

        $processor = $this->getTemplateFilter()
            ->setUseSessionInUrl(false)
            ->setPlainTemplateMode($this->isPlain())
            ->setIsChildTemplate($this->getIsChildTemplate())
            ->setTemplateProcessor([$this, 'getTemplateContent'])
            ->setTemplateModel($this);

        $variables['this'] = $this;

        // Only run app emulation if this is the parent template. Otherwise child will run inside parent emulation.
        if (!$this->getIsChildTemplate()) {
            $this->_applyDesignConfig();
        }

        if ($this->_storeManager->hasSingleStore()) {
            $storeId = $this->_storeManager->getStore()->getId();
        } else {
            $storeId = $this->getDesignConfig()->getStore();
        }
        $processor->setStoreId($storeId);

        $variables = $this->_addEmailVariables($variables, $storeId);
        $processor->setVariables($variables);

        try {
            $result = $processor->filter($this->getTemplateText());
            if ($usePreprocess && $this->isPreprocessed()) {
                return $this->getPreparedTemplateText($result, true);
            }
        } catch (\Exception $e) {
            if (!$this->getIsChildTemplate()) {
                $this->_cancelDesignConfig();
            }
            throw new \Magento\Framework\Exception\MailException(__($e->getMessage()), $e);
        }
        $processedResult = $this->getPreparedTemplateText($result);

        if (!$this->getIsChildTemplate()) {
            $this->_cancelDesignConfig();
        }
        return $processedResult;
    }

    /**
     * Makes additional text preparations for HTML templates
     *
     * @param bool $usePreprocess Use Preprocessed text or original text
     * @param string $html
     * @return string
     */
    public function getPreparedTemplateText($html, $usePreprocess = false)
    {
        if ($usePreprocess) {
            $html = $this->getTemplateTextPreprocessed();
        }

        if ($this->_preprocessFlag || $this->isPlain()) {
            return $html;
        }

        return $this->_applyInlineCss($html);
    }

    /**
     * Retrieve processed template subject
     *
     * @param array $variables
     * @return string
     */
    public function getProcessedTemplateSubject(array $variables)
    {
        $variables['this'] = $this;

        return $this->getTemplateFilter()
            ->setVariables($variables)
            ->filter($this->getTemplateSubject());
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
