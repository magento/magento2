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
 * @package     Mage_Core
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Template model
 *
 * Example:
 *
 * // Loading of template
 * $emailTemplate  = Mage::getModel('Mage_Core_Model_Email_Template')
 *    ->load(Mage::getStoreConfig('path_to_email_template_id_config'));
 * $variables = array(
 *    'someObject' => Mage::getSingleton('Mage_Core_Model_Resource_Email_Template')
 *    'someString' => 'Some string value'
 * );
 * $emailTemplate->send('some@domain.com', 'Name Of User', $variables);
 *
 * @method Mage_Core_Model_Resource_Email_Template _getResource()
 * @method Mage_Core_Model_Resource_Email_Template getResource()
 * @method string getTemplateCode()
 * @method Mage_Core_Model_Email_Template setTemplateCode(string $value)
 * @method string getTemplateText()
 * @method Mage_Core_Model_Email_Template setTemplateText(string $value)
 * @method string getTemplateStyles()
 * @method Mage_Core_Model_Email_Template setTemplateStyles(string $value)
 * @method int getTemplateType()
 * @method Mage_Core_Model_Email_Template setTemplateType(int $value)
 * @method string getTemplateSubject()
 * @method Mage_Core_Model_Email_Template setTemplateSubject(string $value)
 * @method string getTemplateSenderName()
 * @method Mage_Core_Model_Email_Template setTemplateSenderName(string $value)
 * @method string getTemplateSenderEmail()
 * @method Mage_Core_Model_Email_Template setTemplateSenderEmail(string $value)
 * @method string getAddedAt()
 * @method Mage_Core_Model_Email_Template setAddedAt(string $value)
 * @method string getModifiedAt()
 * @method Mage_Core_Model_Email_Template setModifiedAt(string $value)
 * @method string getOrigTemplateCode()
 * @method Mage_Core_Model_Email_Template setOrigTemplateCode(string $value)
 * @method string getOrigTemplateVariables()
 * @method Mage_Core_Model_Email_Template setOrigTemplateVariables(string $value)
 *
 * @category    Mage
 * @package     Mage_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Core_Model_Email_Template extends Mage_Core_Model_Template
{
    /**
     * Configuration path for default email templates
     *
     */
    const XML_PATH_TEMPLATE_EMAIL          = 'global/template/email';
    const XML_PATH_SENDING_SET_RETURN_PATH = 'system/smtp/set_return_path';
    const XML_PATH_SENDING_RETURN_PATH_EMAIL = 'system/smtp/return_path_email';

    protected $_templateFilter;
    protected $_preprocessFlag = false;
    protected $_bcc = array();
    protected $_returnPath = '';
    protected $_replyTo = '';

    /**
     * @var Exception|null
     */
    protected $_sendingException = null;

    static protected $_defaultTemplates;

    /**
     * Initialize email template model
     *
     */
    protected function _construct()
    {
        $this->_init('Mage_Core_Model_Resource_Email_Template');
    }

    /**
     * Get new Zend_Mail instance
     *
     * @return Zend_Mail
     */
    protected function _getMail()
    {
        return new Zend_Mail('utf-8');
    }

    /**
     * Declare template processing filter
     *
     * @param   Varien_Filter_Template $filter
     * @return  Mage_Core_Model_Email_Template
     */
    public function setTemplateFilter(Varien_Filter_Template $filter)
    {
        $this->_templateFilter = $filter;
        return $this;
    }

    /**
     * Get filter object for template processing log
     *
     * @return Mage_Core_Model_Email_Template_Filter
     */
    public function getTemplateFilter()
    {
        if (empty($this->_templateFilter)) {
            $this->_templateFilter = Mage::getModel('Mage_Core_Model_Email_Template_Filter');
            $this->_templateFilter->setUseAbsoluteLinks($this->getUseAbsoluteLinks())
                ->setStoreId($this->getDesignConfig()->getStore());
        }
        return $this->_templateFilter;
    }

    /**
     * Load template by code
     *
     * @param   string $templateCode
     * @return   Mage_Core_Model_Email_Template
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
     * @param string $locale
     */
    public function loadDefault($templateId)
    {
        $defaultTemplates = self::getDefaultTemplates();
        if (!isset($defaultTemplates[$templateId])) {
            return $this;
        }

        $data = &$defaultTemplates[$templateId];
        $this->setTemplateType($data['type']=='html' ? self::TYPE_HTML : self::TYPE_TEXT);

        $module = Mage::getConfig()->determineOmittedNamespace($data['@']['module'], true);
        $templateText = $this->loadBaseContents($module, $data['file']);

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
         * Remove comment lines
         */
        $templateText = preg_replace('#\{\*.*\*\}#suU', '', $templateText);

        $this->setTemplateText($templateText);
        $this->setId($templateId);

        return $this;
    }

    /**
     * Look for base template and read its contents
     *
     * @param string $module A fully qualified module name (<Namespace>_<Name>)
     * @param string $filename File path relative to module/view folder
     * @return string
     * @throws Exception if the requested filename is not found
     */
    public function loadBaseContents($module, $filename)
    {
        $includeFilename = Mage::getConfig()->getModuleDir('view', $module) . DIRECTORY_SEPARATOR . $filename;
        $contents = file_get_contents($includeFilename);
        if (!$contents) {
            throw new Exception(sprintf('Failed to include file "%s".', $includeFilename));
        }
        return $contents;
    }

    /**
     * Retrive default templates from config
     *
     * @return array
     */
    static public function getDefaultTemplates()
    {
        if(is_null(self::$_defaultTemplates)) {
            self::$_defaultTemplates = Mage::getConfig()->getNode(self::XML_PATH_TEMPLATE_EMAIL)->asArray();
        }

        return self::$_defaultTemplates;
    }

    /**
     * Get default templates as options array
     *
     * @return array
     */
    static public function getDefaultTemplatesAsOptionsArray()
    {
        $options = array(array('value'=> '', 'label'=> '', 'group' => ''));
        $groups = array();
        foreach (self::getDefaultTemplates() as $templateId => $row) {
            $module = $row['@']['module'];
            $moduleFullName = Mage::getConfig()->determineOmittedNamespace($module, true);
            $options[] = array(
                'value' => $templateId,
                'label' => Mage::helper($module)->__($row['label']),
                'group' => $moduleFullName,
            );
            $groups[$module] = 1;
        }
        uasort($options, function($a, $b) {
            $key = 'label';
            if ($a[$key] == $b[$key]) {
                return 0;
            }
            return ($a[$key] < $b[$key]) ? -1 : 1;
        });

        return $options;
    }

    /**
     * Return template id
     * return int|null
     */
    public function getId()
    {
        return $this->getTemplateId();
    }

    /**
     * Set id of template
     * @param int $value
     */
    public function setId($value)
    {
        return $this->setTemplateId($value);
    }

    /**
     * Return true if this template can be used for sending queue as main template
     *
     * @return boolean
     */
    public function isValidForSend()
    {
        return !Mage::getStoreConfigFlag('system/smtp/disable')
            && $this->getSenderName()
            && $this->getSenderEmail()
            && $this->getTemplateSubject();
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
     * Process email template code
     *
     * @param   array $variables
     * @return  string
     */
    public function getProcessedTemplate(array $variables = array())
    {
        $processor = $this->getTemplateFilter();
        $processor->setUseSessionInUrl(false)
            ->setPlainTemplateMode($this->isPlain());

        if(!$this->_preprocessFlag) {
            $variables['this'] = $this;
        }

        if(isset($variables['subscriber']) && ($variables['subscriber'] instanceof Mage_Newsletter_Model_Subscriber)) {
            $processor->setStoreId($variables['subscriber']->getStoreId());
        }

        $processor->setIncludeProcessor(array($this, 'getInclude'))
            ->setVariables($variables);

        $this->_applyDesignConfig();
        $storeId = $this->getDesignConfig()->getStore();
        try {
            $processedResult = $processor->setStoreId($storeId)
                ->filter($this->getPreparedTemplateText());
        } catch (Exception $e) {
            $this->_cancelDesignConfig();
            throw $e;
        }
        $this->_cancelDesignConfig();
        return $processedResult;
    }

    /**
     * Makes additional text preparations for HTML templates
     *
     * @return string
     */
    public function getPreparedTemplateText()
    {
        if ($this->isPlain() || !$this->getTemplateStyles()) {
            return $this->getTemplateText();
        }
        // wrap styles into style tag
        $html = "<style type=\"text/css\">\n%s\n</style>\n%s";
        return sprintf($html, $this->getTemplateStyles(), $this->getTemplateText());
    }

    /**
     * Get template code for include directive
     *
     * @param   string $template
     * @param   array $variables
     * @return  string
     */
    public function getInclude($template, array $variables)
    {
        $thisClass = __CLASS__;
        $includeTemplate = new $thisClass();

        $includeTemplate->loadByCode($template);

        return $includeTemplate->getProcessedTemplate($variables);
    }

    /**
     * Send mail to recipient
     *
     * @param   array|string       $email        E-mail(s)
     * @param   array|string|null  $name         receiver name(s)
     * @param   array              $variables    template variables
     * @return  boolean
     **/
    public function send($email, $name = null, array $variables = array())
    {
        if (!$this->isValidForSend()) {
            Mage::logException(new Exception('This letter cannot be sent.')); // translation is intentionally omitted
            return false;
        }

        $emails = array_values((array)$email);
        $names = is_array($name) ? $name : (array)$name;
        $names = array_values($names);
        foreach ($emails as $key => $email) {
            if (!isset($names[$key])) {
                $names[$key] = substr($email, 0, strpos($email, '@'));
            }
        }

        $variables['email'] = reset($emails);
        $variables['name'] = reset($names);

        ini_set('SMTP', Mage::getStoreConfig('system/smtp/host'));
        ini_set('smtp_port', Mage::getStoreConfig('system/smtp/port'));

        $mail = $this->_getMail();
        foreach ($this->_bcc as $bcc) {
            $mail->addBcc($bcc);
        }
        if ($this->_returnPath) {
            $mail->setReturnPath($this->_returnPath);
        }
        if ($this->_replyTo) {
            $mail->setReplyTo($this->_replyTo);
        }

        $setReturnPath = Mage::getStoreConfig(self::XML_PATH_SENDING_SET_RETURN_PATH);
        switch ($setReturnPath) {
            case 1:
                $returnPathEmail = $this->getSenderEmail();
                break;
            case 2:
                $returnPathEmail = Mage::getStoreConfig(self::XML_PATH_SENDING_RETURN_PATH_EMAIL);
                break;
            default:
                $returnPathEmail = null;
                break;
        }

        if ($returnPathEmail !== null) {
            $mailTransport = new Zend_Mail_Transport_Sendmail("-f".$returnPathEmail);
            Zend_Mail::setDefaultTransport($mailTransport);
        }

        foreach ($emails as $key => $email) {
            $mail->addTo($email, '=?utf-8?B?' . base64_encode($names[$key]) . '?=');
        }

        $this->setUseAbsoluteLinks(true);
        $text = $this->getProcessedTemplate($variables, true);

        if ($this->isPlain()) {
            $mail->setBodyText($text);
        } else {
            $mail->setBodyHTML($text);
        }

        $mail->setSubject('=?utf-8?B?' . base64_encode($this->getProcessedTemplateSubject($variables)) . '?=');
        $mail->setFrom($this->getSenderEmail(), $this->getSenderName());

        $result = false;
        $this->_sendingException = null;
        try {
            $mail->send();
            $result = true;
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_sendingException = $e;
        }
        $this->_bcc = array();
        $this->_returnPath = '';
        $this->_replyTo = '';

        return $result;
    }

    /**
     * Get exception, generated during send() method
     *
     * @return Exception|null
     */
    public function getSendingException()
    {
        return $this->_sendingException;
    }

    /**
     * Send transactional email to recipient
     *
     * @param   int|string $templateId
     * @param   string|array $sender sender information, can be declared as part of config path
     * @param   string $email recipient email
     * @param   string $name recipient name
     * @param   array $vars variables which can be used in template
     * @param   int|null $storeId
     * @return  Mage_Core_Model_Email_Template
     */
    public function sendTransactional($templateId, $sender, $email, $name, $vars=array(), $storeId=null)
    {
        $this->setSentSuccess(false);
        if (($storeId === null) && $this->getDesignConfig()->getStore()) {
            $storeId = $this->getDesignConfig()->getStore();
        }

        if (is_numeric($templateId)) {
            $this->load($templateId);
        } else {
            $this->loadDefault($templateId);
        }

        if (!$this->getId()) {
            throw Mage::exception('Mage_Core', Mage::helper('Mage_Core_Helper_Data')->__('Invalid transactional email code: "%s"', $templateId));
        }

        if (!is_array($sender)) {
            $this->setSenderName(Mage::getStoreConfig('trans_email/ident_'.$sender.'/name', $storeId));
            $this->setSenderEmail(Mage::getStoreConfig('trans_email/ident_'.$sender.'/email', $storeId));
        } else {
            $this->setSenderName($sender['name']);
            $this->setSenderEmail($sender['email']);
        }

        if (!isset($vars['store'])) {
            $vars['store'] = Mage::app()->getStore($storeId);
        }

        $this->setSentSuccess($this->send($email, $name, $vars));
        return $this;
    }

    /**
     * Process email subject
     *
     * @param   array $variables
     * @return  string
     */
    public function getProcessedTemplateSubject(array $variables)
    {
        $processor = $this->getTemplateFilter();

        if(!$this->_preprocessFlag) {
            $variables['this'] = $this;
        }

        $processor->setVariables($variables);

        $this->_applyDesignConfig();
        $storeId = $this->getDesignConfig()->getStore();
        try{
            $processedResult = $processor->setStoreId($storeId)
                ->filter($this->getTemplateSubject());
        }
        catch (Exception $e) {
            $this->_cancelDesignConfig();
            throw $e;
        }
        $this->_cancelDesignConfig();
        return $processedResult;
    }

    /**
     * Add email BCC
     *
     * @param string|array $bcc
     * @return Mage_Core_Model_Email_Template
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
     * @return Mage_Core_Model_Email_Template
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
     * @return Mage_Core_Model_Email_Template
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
        $variables = array();
        if ($variablesString && is_string($variablesString)) {
            $variablesString = str_replace("\n", '', $variablesString);
            $variables = Zend_Json::decode($variablesString);
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
        $optionArray = array();
        $variables = $this->_parseVariablesString($this->getData('orig_template_variables'));
        if ($variables) {
            foreach ($variables as $value => $label) {
                $optionArray[] = array(
                    'value' => '{{' . $value . '}}',
                    'label' => Mage::helper('Mage_Core_Helper_Data')->__('%s', $label)
                );
            }
            if ($withGroup) {
                $optionArray = array(
                    'label' => Mage::helper('Mage_Core_Helper_Data')->__('Template Variables'),
                    'value' => $optionArray
                );
            }
        }
        return $optionArray;
    }

    /**
     * Validate email template code
     *
     * @return Mage_Core_Model_Email_Template
     */
    protected function _beforeSave()
    {
        $code = $this->getTemplateCode();
        if (empty($code)) {
            Mage::throwException(Mage::helper('Mage_Core_Helper_Data')->__('The template Name must not be empty.'));
        }
        if($this->_getResource()->checkCodeUsage($this)) {
            Mage::throwException(Mage::helper('Mage_Core_Helper_Data')->__('Duplicate Of Template Name'));
        }
        return parent::_beforeSave();
    }
}
