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
 * @category    Mage
 * @package     Mage_Newsletter
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Template model
 *
 * @method Mage_Newsletter_Model_Resource_Template _getResource()
 * @method Mage_Newsletter_Model_Resource_Template getResource()
 * @method string getTemplateCode()
 * @method Mage_Newsletter_Model_Template setTemplateCode(string $value)
 * @method Mage_Newsletter_Model_Template setTemplateText(string $value)
 * @method Mage_Newsletter_Model_Template setTemplateTextPreprocessed(string $value)
 * @method string getTemplateStyles()
 * @method Mage_Newsletter_Model_Template setTemplateStyles(string $value)
 * @method int getTemplateType()
 * @method Mage_Newsletter_Model_Template setTemplateType(int $value)
 * @method string getTemplateSubject()
 * @method Mage_Newsletter_Model_Template setTemplateSubject(string $value)
 * @method string getTemplateSenderName()
 * @method Mage_Newsletter_Model_Template setTemplateSenderName(string $value)
 * @method string getTemplateSenderEmail()
 * @method Mage_Newsletter_Model_Template setTemplateSenderEmail(string $value)
 * @method int getTemplateActual()
 * @method Mage_Newsletter_Model_Template setTemplateActual(int $value)
 * @method string getAddedAt()
 * @method Mage_Newsletter_Model_Template setAddedAt(string $value)
 * @method string getModifiedAt()
 * @method Mage_Newsletter_Model_Template setModifiedAt(string $value)
 *
 * @category    Mage
 * @package     Mage_Newsletter
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Newsletter_Model_Template extends Mage_Core_Model_Template
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
     * @var Zend_Mail
     */
    protected $_mail;

    /**
     * Initialize resource model
     *
     */
    protected function _construct()
    {
        $this->_init('Mage_Newsletter_Model_Resource_Template');
    }

    /**
     * Validate Newsletter template
     *
     * @throws Mage_Core_Exception
     * @return bool
     */
    public function validate()
    {
        $validators = array(
            'template_code'         => array(Zend_Filter_Input::ALLOW_EMPTY => false),
            'template_type'         => 'Int',
            'template_sender_email' => 'EmailAddress',
            'template_sender_name'  => array(Zend_Filter_Input::ALLOW_EMPTY => false)
        );
        $data = array();
        foreach (array_keys($validators) as $validateField) {
            $data[$validateField] = $this->getDataUsingMethod($validateField);
        }

        $validateInput = new Zend_Filter_Input(array(), $validators, $data);
        if (!$validateInput->isValid()) {
            $errorMessages = array();
            foreach ($validateInput->getMessages() as $messages) {
                if (is_array($messages)) {
                    foreach ($messages as $message) {
                        $errorMessages[] = $message;
                    }
                }
                else {
                    $errorMessages[] = $messages;
                }
            }

            Mage::throwException(join("\n", $errorMessages));
        }
    }

    /**
     * Processing object before save data
     *
     * @return Mage_Newsletter_Model_Template
     */
    protected function _beforeSave()
    {
        $this->validate();
        return parent::_beforeSave();
    }

    /**
     * Load template by code
     *
     * @param string $templateCode
     * @return Mage_Newsletter_Model_Template
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
    public function getType(){
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
    public function getProcessedTemplate(array $variables = array(), $usePreprocess = false)
    {
        /* @var $processor Mage_Newsletter_Model_Template_Filter */
        $processor = Mage::helper('Mage_Newsletter_Helper_Data')->getTemplateProcessor();

        if (!$this->_preprocessFlag) {
            $variables['this'] = $this;
        }

        if (Mage::app()->isSingleStoreMode()) {
            $processor->setStoreId(Mage::app()->getStore());
        } else {
            $processor->setStoreId(Mage::app()->getRequest()->getParam('store_id'));
        }

        $processor
            ->setIncludeProcessor(array($this, 'getInclude'))
            ->setVariables($variables);

        if ($usePreprocess && $this->isPreprocessed()) {
            return $processor->filter($this->getPreparedTemplateText(true));
        }

        return $processor->filter($this->getPreparedTemplateText());
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
        return Mage::getModel('Mage_Newsletter_Model_Template')
            ->loadByCode($templateCode)
            ->getProcessedTemplate($variables);
    }

    /**
     * Retrieve processed template subject
     *
     * @param array $variables
     * @return string
     */
    public function getProcessedTemplateSubject(array $variables)
    {
        $processor = new Varien_Filter_Template();

        if (!$this->_preprocessFlag) {
            $variables['this'] = $this;
        }

        $processor->setVariables($variables);
        return $processor->filter($this->getTemplateSubject());
    }

    /**
     * Retrieve template text wrapper
     *
     * @return string
     */
    public function getTemplateText()
    {
        if (!$this->getData('template_text') && !$this->getId()) {
            $this->setData('template_text',
                Mage::helper('Mage_Newsletter_Helper_Data')->__('Follow this link to unsubscribe <!-- This tag is for unsubscribe link  --><a href="{{var subscriber.getUnsubscriptionLink()}}">{{var subscriber.getUnsubscriptionLink()}}</a>')
            );
        }

        return $this->getData('template_text');
    }
}
