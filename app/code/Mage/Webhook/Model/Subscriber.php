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
 * @package     Mage_Webhook
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * Subscriber model
 *
 * @method string getName()
 * @method Mage_Webhook_Model_Subscriber setName(string $value)
 * @method string getEndpointUrl()
 * @method Mage_Webhook_Model_Subscriber setEndpointUrl(string $value)
 * @method string getUpdatedAt()
 * @method Mage_Webhook_Model_Subscriber setUpdatedAt(string $value)
 * @method int getStatus()
 * @method Mage_Webhook_Model_Subscriber setStatus(int $value)
 * @method string getVersion()
 * @method Mage_Webhook_Model_Subscriber setVersion(string $value)
 * @method string getExtentionId()
 * @method Mage_Webhook_Model_Subscriber setExtentionId(string $value)
 * @method string getMapping()
 * @method Mage_Webhook_Model_Subscriber setMapping(string $value)
 * @method string getFormat()
 * @method Mage_Webhook_Model_Subscriber setFormat(string $value)
 * @method string getTopics()
 * @method Mage_Webhook_Model_Subscriber setTopics(string $value)
 */
class Mage_Webhook_Model_Subscriber extends Mage_Core_Model_Abstract
{
    /**
     * Authentication type
     *
     * @var string
     */
    const AUTH_TYPE_WS_SECURITY = 'WS-Security';
    const AUTH_TYPE_HMAC = "hmac";
    const AUTH_TYPE_BASIC = "basic";
    const AUTH_TYPE_NONE = "none";

    /**
     * Registration mechanism
     */
    const REGISTRATION_MECHANISM_MANUAL = 'manual';

    /**
     * Data content mapping
     *
     * @var string
     */
    const MAPPING_DEFAULT = "default";

    /**
     * Data format
     *
     * @var string
     */
    const FORMAT_JSON = "json";
    const FORMAT_XML = "xml";

    /**
     * Subscriber statuses
     * @var int
     */
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_REVOKED = 2;

    const XML_PATH_AUTHENTICATION_TYPES = 'global/webhook/authentication_types';

    /**
     * Authentication model for current subscriber
     *
     * @var Mage_Webhook_Model_Authentication_Interface
     */
    protected $_authenticationModel;

    /**
     * Initialize model
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('Mage_Webhook_Model_Resource_Subscriber');
    }

    /**
     * Load the Webapi user model for specified subscriber
     *
     * @return Mage_Webapi_Model_Acl_User
     */
    public function getApiUser()
    {
        return Mage::getModel('Mage_Webapi_Model_Acl_User')->load($this->getApiUserId());
    }

    /**
     * Create new API User and Role for the subscriber's needs
     *
     * @param string $email
     * @param string $key
     * @param string $secret
     * @param string $company
     * @throws Exception
     */
    public function createUserAndRole($email, $key, $secret, $company)
    {
        Mage::getModel('Mage_Webhook_Model_Subscriber_Webapi', $this)
            ->createUserAndRole($email, $key, $secret, $company);

        return $this;
    }

    /**
     * Determines if the subscriber is subscribed to a topic.
     *
     * @param string $topic the topic to check
     * @return boolean true if subscribed, false otherwise
     */
    public function isSubscribedToTopic($topic)
    {
        return in_array($topic, $this->getTopics());
    }

    /**
     * Return a list of acl permissions that are required for this subscribers subscriptions.
     */
    public function getRequiredPermissions()
    {
        $aclConfig = Mage::getModel('Mage_Webhook_Model_Authorization_Config');
        $aclPermissions = array();
        foreach ($this->getTopics() as $topic) {
            $aclPermissions[$aclConfig->getParentFromTopic($topic)] = TRUE;
        }

        return array_keys($aclPermissions);
    }

    /**
     * Get auth model which is used by the subscriber
     *
     * @return Mage_Webhook_Model_Authentication_Interface
     */
    public function getAuthenticationModel()
    {
        if (!$this->_authenticationModel) {
            $modelClass = (string) Mage::getConfig()
                ->getNode(self::XML_PATH_AUTHENTICATION_TYPES . '/' . $this->getAuthenticationType() . '/class');
            $this->_authenticationModel = Mage::getModel($modelClass);

            if (!$this->_authenticationModel instanceof Mage_Webhook_Model_Authentication_Interface) {
                throw new LogicException(
                    "Can't load authentication model '{$this->getAuthenticationType()}' for subscriber with ID {$this->getId()}."
                );
            }
        }

        return $this->_authenticationModel;
    }

    /**
     * Prepare data to be saved to database
     * @return Mage_Core_Model_Abstract
     * @throws Mage_Webhook_Exception
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();
        if (!$this->hasStatus()) {
            $this->setStatus(Mage_Webhook_Model_Subscriber::STATUS_INACTIVE);
        }

        if (!$this->hasAuthenticationType()) {
            $this->setAuthenticationType(self::AUTH_TYPE_NONE);
        }

        if(!$this->hasRegistrationMechanism()) {
            $this->setRegistrationMechanism(self::REGISTRATION_MECHANISM_MANUAL);
        }

        if ($this->hasDataChanges()) {
            $this->setUpdatedAt($this->_getResource()->formatDate(time()));
        }

        return $this;
    }

    /** 
     *  This was being used to filter out callback topics that were already subscribed by someone.  No longer the case.
     *  Limits are no longer tied to callback topics
     *  TODO: Refactor how limits work
     */
    protected function _filterOutRestrictedTopics()
    {
        $notAllowedTopics = $this->getSubscribedCallbackTopics();

        if ($this->getApiUserId()) {
            if (!$this->getIsNewlyActivated()) {
                $existing = $this->getOrigData('topics');
                $existing = is_null($existing) ? array() : $existing;
                $notAllowedTopics = array_diff($notAllowedTopics, $existing);
            }

            if (!empty($notAllowedTopics) && !$this->getIsNewlyActivated()) {
                throw Mage::exception('Mage_Core', 'Cannot save callback which already has subscriber');
            }
            $topics = $this->getTopics();
            $topics = array_diff($topics, $notAllowedTopics);
            $this->setTopics($topics);
        }
    }

    /**
     * Load object data
     *
     * @param   integer $id
     * @return  Mage_Core_Model_Abstract
     */
    public function load($id, $field=null)
    {
        parent::load($id, $field);

        if (is_null($this->getApiUserId())) {
            $this->setStatus(self::STATUS_INACTIVE);
        }

        return $this;
    }

    public function getAvailableHooks()
    {
        $node = Mage::getConfig()->getNode(Mage_Webhook_Model_Source_Hook::XML_PATH_WEBHOOK);
        $availableHooks = array();
        foreach ($node->asArray() as $key => $hookNode) {
            foreach ($hookNode as $name => $hook) {
                if (is_array($hook)) {
                    $availableHooks[] = $key . '/' . $name;
                }
            }
            if ($hook['label']) {
                $availableHooks[] = $key;
            }
        }

        return $availableHooks;
    }

    public function validate() {
        Mage::getModel('Mage_Webhook_Model_Subscriber_Webapi', $this)->validate();
        return $this;
    }

    /**
         * Gets callback topics
         *
         * @return array
         */
    protected function _getCallbackTopics()
    {
        $topics = $this->getTopics();
        $callbackHooks = array();
        foreach($topics as $topicName) {
            $node = Mage::getConfig()->getNode(Mage_Webhook_Model_Source_Hook::XML_PATH_WEBHOOK . '/' . $topicName);
            if ($node && $node->type == 'callback') {
                $callbackHooks[] = $topicName;
            }
        }
        return $callbackHooks;
    }

    /**
     * Gets callback topics which already have subscriber
     *
     * @return array
     */
    public function getSubscribedCallbackTopics()
    {
        $topics = $this->_getCallbackTopics();
        return $this->_getResource()->getSubscribedCallbackTopics($topics);
    }
}
