<?php
/**
 * Represents a subscription to one or more topics
 *
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
 *
 * @method string getName()
 * @method Mage_Webhook_Model_Subscription setName(string $value)
 * @method Mage_Webhook_Model_Subscription setEndpointId(string $value)
 * @method string getEndpointId()
 * @method string getUpdatedAt()
 * @method Mage_Webhook_Model_Subscription setUpdatedAt(string $value)
 * @method Mage_Webhook_Model_Subscription setStatus(int $value)
 * @method string getVersion()
 * @method Mage_Webhook_Model_Subscription setVersion(string $value)
 * @method string getAlias()
 * @method Mage_Webhook_Model_Subscription setAlias(string $value)
 * @method Mage_Webhook_Model_Subscription setTopics(array $value)
 * @method Mage_Webhook_Model_Subscription setRegistrationMechanism(string $value)
 * @method string getRegistrationMechanism()
 * @method bool hasRegistrationMechanism()
 * @method bool hasStatus()
 * @method int getSubscriptionId()
 */
class Mage_Webhook_Model_Subscription
    extends Mage_Core_Model_Abstract
    implements Magento_PubSub_SubscriptionInterface
{
    const FIELD_ENDPOINT_URL = 'endpoint_url';
    const FIELD_FORMAT = 'format';
    const FIELD_AUTHENTICATION_TYPE = 'authentication_type';
    const FIELD_API_USER_ID = 'api_user_id';
    const FIELD_TIMEOUT_IN_SECS = 'timeout_in_secs';
    /**
     * Registration mechanism
     */
    const REGISTRATION_MECHANISM_MANUAL = 'manual';


    /**
     * @var Mage_Webhook_Model_Endpoint
     */
    private $_endpoint = null;

    /**
     * Tracks whether or not we've already loaded endpoint data from the DB.
     *
     * @var bool
     */
    private $_endpointLoaded = false;

    /**
     * @param Mage_Webhook_Model_Endpoint $endpoint
     * @param Mage_Core_Model_Context $context
     * @param Mage_Core_Model_Resource_Abstract $resource
     * @param Varien_Data_Collection_Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        Mage_Webhook_Model_Endpoint $endpoint,
        Mage_Core_Model_Context $context,
        Mage_Core_Model_Resource_Abstract $resource = null,
        Varien_Data_Collection_Db $resourceCollection = null,
        array $data = array()
    ) {
        parent::__construct($context, $resource, $resourceCollection, $data);

        $this->_endpoint = $endpoint;
    }

    /**
     * Initialize model
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('Mage_Webhook_Model_Resource_Subscription');
    }

    /**
     * Prepare data to be saved to database
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _beforeSave()
    {
        if (!$this->hasStatus()) {
            $this->setStatus(Magento_PubSub_SubscriptionInterface::STATUS_INACTIVE);
        }

        // TODO: Can this ever be set to anything else, is it being used?
        if (!$this->hasRegistrationMechanism()) {
            $this->setRegistrationMechanism(self::REGISTRATION_MECHANISM_MANUAL);
        }

        if ($this->_endpoint->hasDataChanges()) {
            $this->_endpoint->save();
            if ($this->getEndpointId() === null) {
                $this->setEndpointId($this->_endpoint->getId());
            }
        }

        if ($this->hasDataChanges()) {
            $this->setUpdatedAt($this->_getResource()->formatDate(time()));
        }

        return parent::_beforeSave();
    }

    /**
     * Processing object after delete data
     *
     * We need to be sure that related objects like Endpoint are also deleted.
     *
     * @return Mage_Core_Model_Abstract|void
     */
    protected function _afterDelete()
    {
        $this->_getEndpoint()->delete();

        return parent::_afterDelete();
    }

    /**
     * Determines if the subscription is subscribed to a topic.
     *
     * @param string $topic     The topic to check
     * @return boolean          True if subscribed, false otherwise
     */
    public function hasTopic($topic)
    {
        return in_array($topic, $this->getTopics());
    }


    /**
     * Mark this subscription status to activated
     */
    public function activate()
    {
        $this->setStatus(Magento_PubSub_SubscriptionInterface::STATUS_ACTIVE);
    }

    /**
     * Mark this subscription status as deactivated
     */
    public function deactivate()
    {
        $this->setStatus(Magento_PubSub_SubscriptionInterface::STATUS_INACTIVE);
    }

    /**
     * Mark this subscription status to revoked
     */
    public function revoke()
    {
        $this->setStatus(Magento_PubSub_SubscriptionInterface::STATUS_REVOKED);
    }

    /**
     * Checks that the subscription has access to all the resources/topics it has subscribed to.
     *
     * @return string[] array of all invalid topics
     */
    public function findRestrictedTopics()
    {
        $restrictedTopics = array();
        $user = $this->getUser();
        if (null === $user) {
            return $restrictedTopics;
        }
        foreach ($this->getTopics() as $topic) {
            if (!$user->hasPermission($topic)) {
                $restrictedTopics[] = $topic;
            }
        }

        return $restrictedTopics;
    }

    /**
     * Returns the endpoint to which messages will be sent
     *
     * @return Mage_Webhook_Model_Endpoint
     */
    private function _getEndpoint()
    {
        if (!$this->_endpointLoaded && $this->getEndpointId() !== null) {
            $this->_endpoint->load($this->getEndpointId());
            $this->_endpointLoaded = true;
        }
        return $this->_endpoint;
    }

    /**
     * Overwrite data in the object.
     *
     * If $key is string, the attribute value will be overwritten by $value
     *
     * If $key is an array, it will overwrite all the data in the object.
     *
     * @param string|array  $key
     * @param mixed         $value
     * @return Mage_Webhook_Model_Subscription
     */
    public function setData($key, $value = null)
    {
        parent::setData($key, $value);

        if (is_array($key)) {
            $this->_setDataArray($key);
        } else {
            switch ($key) {
                case self::FIELD_ENDPOINT_URL:
                    $this->setEndpointUrl($value);
                    break;
                case self::FIELD_FORMAT:
                    $this->setFormat($value);
                    break;
                case self::FIELD_AUTHENTICATION_TYPE:
                    $this->setAuthenticationType($value);
                    break;
                case self::FIELD_API_USER_ID:
                    $this->setApiUserId($value);
                    break;
                case self::FIELD_TIMEOUT_IN_SECS:
                    $this->setTimeoutInSecs($value);
                    break;
            }
        }

        return $this;
    }

    /**
     * Set the endpoint URL for this Subscription
     *
     * @param string $url
     * @return Mage_Webhook_Model_Subscription
     */
    public function setEndpointUrl($url)
    {
        $this->_getEndpoint()->setEndpointUrl($url);
        $this->setDataChanges(true);
        return $this;
    }

    /**
     * Set the endpoint timeout in seconds.
     *
     * @param int $timeout
     * @return Mage_Webhook_Model_Subscription
     */
    public function setTimeoutInSecs($timeout)
    {
        $this->_getEndpoint()->setTimeoutInSecs($timeout);
        $this->setDataChanges(true);
        return $this;
    }

    /**
     * Set the format in which data should be sent (json, xml)
     *
     * @param string $format
     * @return Mage_Webhook_Model_Subscription
     */
    public function setFormat($format)
    {
        $this->_getEndpoint()->setFormat($format);
        $this->setDataChanges(true);
        return $this;
    }

    /**
     * Set the api user id that this subscription is associated with
     *
     * @param string $userId
     * @return Mage_Webhook_Model_Subscription
     */
    public function setApiUserId($userId)
    {
        $this->_getEndpoint()->setApiUserId($userId);
        $this->setDataChanges(true);
        return $this;
    }

    /**
     * Set the authentication type for this subscription
     *
     * @param string $authType
     * @return Mage_Webhook_Model_Subscription
     */
    public function setAuthenticationType($authType)
    {
        $this->_getEndpoint()->setAuthenticationType($authType);
        $this->setDataChanges(true);
        return $this;
    }

    /**
     * Returns the user abstraction associated with this subscription or null if no user has been associated yet.
     *
     * @return Magento_Outbound_UserInterface|null
     */
    public function getUser()
    {
        return $this->_getEndpoint()->getUser();
    }

    /**
     * Returns the type of authentication to use when attaching authentication to a message
     *
     * @return string
     */
    public function getAuthenticationType()
    {
        return $this->_getEndpoint()->getAuthenticationType();
    }

    /**
     * Returns the format this message should be sent in (JSON, XML, etc.)
     *
     * @return string
     */
    public function getFormat()
    {
        return $this->_getEndpoint()->getFormat();
    }

    /**
     * Returns the api user id that this subscriptions endpoint is associated with.
     *
     * @return string
     */
    public function getApiUserId()
    {
        return $this->_getEndpoint()->getApiUserId();
    }

    /**
     * Returns the endpoint URL of this subscription
     *
     * @return string
     */
    public function getEndpointUrl()
    {
        return $this->_getEndpoint()->getEndpointUrl();
    }

    /**
     * Returns the maximum time in seconds that this subscription is willing to wait before a retry should be attempted
     *
     * @return int
     */
    public function getTimeoutInSecs()
    {
        return $this->_getEndpoint()->getTimeoutInSecs();
    }

    /**
     * Returns a list of topics that this Subscription is subscribed to
     *
     * @return array string[]
     */
    public function getTopics()
    {
        if (!isset($this->_data['topics'])) {
            $this->_getResource()->loadTopics($this);
        }
        return $this->_getData('topics');
    }

    /**
     * Get the status of this endpoint, which should match one of the constants in Magento_PubSub_SubscriptionInterface
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->_getData('status');
    }

    /**
     * Object data getter
     *
     * If $key is not defined, method will return all the data as an array.
     * Otherwise it will return value of the element specified by $key.
     * It is possible to use keys like a/b/c for access nested array data.
     *
     * If $index is specified it will treat data as an array and retrieve
     * corresponding member. If data is a string - it will be exploded by
     * new line character and converted to array.
     *
     * @param string     $key
     * @param string|int $index
     * @return mixed
     */
    public function getData($key = '', $index = null)
    {
        $data = parent::getData($key, $index);

        switch ($key) {
            case self::FIELD_ENDPOINT_URL:
                return $this->getEndpointUrl();
            case self::FIELD_FORMAT:
                return $this->getFormat();
            case self::FIELD_AUTHENTICATION_TYPE:
                return $this->getAuthenticationType();
            case self::FIELD_API_USER_ID:
                return $this->getApiUserId();
            case self::FIELD_TIMEOUT_IN_SECS:
                return $this->getTimeoutInSecs();
            case '':
                $data[self::FIELD_ENDPOINT_URL] = $this->getEndpointUrl();
                $data[self::FIELD_FORMAT] = $this->getFormat();
                $data[self::FIELD_AUTHENTICATION_TYPE] = $this->getAuthenticationType();
                $data[self::FIELD_API_USER_ID] = $this->getApiUserId();
                $data[self::FIELD_TIMEOUT_IN_SECS] = $this->getTimeoutInSecs();
                return $data;
            default:
                return $data;
        }
    }





    /**
     * Set data by calling setter functions
     *
     * @param array $data
     */
    protected function _setDataArray(array $data)
    {
        if (isset($data[self::FIELD_ENDPOINT_URL])) {
            $this->setEndpointUrl($data[self::FIELD_ENDPOINT_URL]);
        }
        if (isset($data[self::FIELD_FORMAT])) {
            $this->setFormat($data[self::FIELD_FORMAT]);
        }
        if (isset($data[self::FIELD_AUTHENTICATION_TYPE])) {
            $this->setAuthenticationType($data[self::FIELD_AUTHENTICATION_TYPE]);
        }
        if (isset($data[self::FIELD_API_USER_ID])) {
            $this->setApiUserId($data[self::FIELD_API_USER_ID]);
        }
        if (isset($data[self::FIELD_TIMEOUT_IN_SECS])) {
            $this->setTimeoutInSecs($data[self::FIELD_TIMEOUT_IN_SECS]);
        }
    }
}