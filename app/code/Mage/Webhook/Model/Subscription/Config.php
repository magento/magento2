<?php
/**
 * Configures subscriptions based on information from config object
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
 */
class Mage_Webhook_Model_Subscription_Config
{
    /** Webhook subscription configuration path */
    const XML_PATH_SUBSCRIPTIONS = 'global/webhook/subscriptions';

    /** @var Mage_Core_Model_Translate  */
    private $_translator;

    /** @var Mage_Webhook_Model_Resource_Subscription_Collection  */
    protected $_subscriptionSet;

    /** @var  Mage_Core_Model_Config */
    protected $_mageConfig;

    /** @var  Mage_Webhook_Model_Subscription_Factory */
    protected $_subscriptionFactory;

    /** @var Mage_Core_Model_Logger */
    private $_logger;

    /**
     * @param Mage_Core_Model_Translate $translator
     * @param Mage_Webhook_Model_Resource_Subscription_Collection $subscriptionSet
     * @param Mage_Core_Model_Config $mageConfig
     * @param Mage_Webhook_Model_Subscription_Factory $subscriptionFactory
     * @param Mage_Core_Model_Logger $logger
     */
    public function __construct(
        Mage_Core_Model_Translate $translator,
        Mage_Webhook_Model_Resource_Subscription_Collection $subscriptionSet,
        Mage_Core_Model_Config $mageConfig,
        Mage_Webhook_Model_Subscription_Factory $subscriptionFactory,
        Mage_Core_Model_Logger $logger
    ) {
        $this->_translator = $translator;
        $this->_subscriptionSet = $subscriptionSet;
        $this->_mageConfig = $mageConfig;
        $this->_subscriptionFactory = $subscriptionFactory;
        $this->_logger = $logger;
    }

    /**
     * Checks if new subscriptions need to be generated from config files
     *
     * @return Mage_Webhook_Model_Subscription_Config
     */
    public function updateSubscriptionCollection()
    {
        $subscriptionConfig = $this->_getSubscriptionConfigNode();

        if (!empty($subscriptionConfig)) {
            $subscriptionConfig = $subscriptionConfig->asArray();
        }
        // It could be no subscriptions have been defined
        if (!$subscriptionConfig) {
            return $this;
        }

        $errors = array();

        foreach ($subscriptionConfig as $alias => $subscriptionData) {
            if (!$this->_validateConfigData($subscriptionData)) {
                $errors[] = $this->_translator->translate(
                    array("Invalid config data for subscription '%s'.", $alias)
                );
                continue;
            }

            $subscriptions = $this->_subscriptionSet->getSubscriptionsByAlias($alias);
            if (empty($subscriptions)) {
                // add new subscription
                $this->_addSubscriptionFromConfigData($alias, $subscriptionData);
                continue;
            } else {
                // get first subscription from array
                $subscription = current($subscriptions);
            }

            if (isset($subscriptionData['version']) && $subscription->getVersion() != $subscriptionData['version']) {
                // update subscription from config
                $this->_updateSubscriptionFromConfigData($subscription, $subscriptionData);
            }
        }

        if (!empty($errors)) {
            $this->_handleErrors($errors);
        }

        return $this;
    }

    /**
     * Logs errors without causing large failure, since there may be other valid configurations
     *
     * @param array $errors
     */
    protected function _handleErrors(array $errors)
    {
        $this->_logger->logException(new Mage_Webhook_Exception(implode("\n", $errors)));
    }

    /**
     * Gets xml node storing subscription configurations
     *
     * @return Mage_Core_Model_Config_Element
     */
    protected function _getSubscriptionConfigNode()
    {
        return $this->_mageConfig->getNode(self::XML_PATH_SUBSCRIPTIONS);
    }

    /**
     * Validates config data by checking that $data is an array and that 'data' maps to some value
     *
     * @param mixed $data
     * @return bool
     */
    protected function _validateConfigData($data)
    {
        //  We can't demand that every possible value be supplied as some of these can be supplied
        //  at a later point in time using the web API
        return is_array($data) && isset($data['name']);
    }

    /**
     * Creates a new subscription and configures it
     *
     * @param string $alias
     * @param array $configData
     * @return Mage_Core_Model_Abstract
     */
    protected function _addSubscriptionFromConfigData($alias, array $configData)
    {
        /** @var $subscription Mage_Webhook_Model_Subscription */
        $subscription = $this->_createSubscription($alias);
        return $this->_updateSubscriptionFromConfigData($subscription, $configData);
    }

    /**
     * Creates a new subscription
     *
     * @param string $alias
     * @return Mage_Webhook_Model_Subscription
     */
    protected function _createSubscription($alias)
    {
        $subscription = $this->_subscriptionFactory->create()
            ->setAlias($alias)
            ->setStatus(Mage_Webhook_Model_Subscription::STATUS_INACTIVE);
        return $subscription;
    }

    /**
     * Configures a subscription
     *
     * @param Mage_Webhook_Model_Subscription $subscription
     * @param array $configData
     * @return Mage_Core_Model_Abstract
     */
    protected function _updateSubscriptionFromConfigData(
        Mage_Webhook_Model_Subscription $subscription,
        array $configData
    ) {
        $subscription->setName($configData['name'])
            ->setFormat($this->_get($configData, 'format', Magento_Outbound_EndpointInterface::FORMAT_JSON))
            ->setVersion($this->_get($configData, 'version'))
            ->setEndpointUrl($this->_get($configData, 'endpoint_url'))
            ->setTopics(isset($configData['topics']) ? $this->_getTopicsFlatList($configData['topics']) : array())
            ->setAuthenticationType(
                isset($configData['authentication']['type'])
                ? $configData['authentication']['type']
                : Magento_Outbound_EndpointInterface::AUTH_TYPE_NONE
            )
            ->setRegistrationMechanism(
                isset($configData['registration_mechanism'])
                ? $configData['registration_mechanism']
                : Mage_Webhook_Model_Subscription::REGISTRATION_MECHANISM_MANUAL
            );

        return $subscription->save();
    }

    /**
     * Returns data from array or default if data does not exist
     *
     * @param array $array
     * @param int|string $key
     * @param mixed $default
     * @return mixed|null
     */
    private function _get($array, $key, $default=null)
    {
        if (isset($array[$key])) {
            return $array[$key];
        } else {
            return $default;
        }
    }

    /**
     * Convert topics into acceptable form for subscription
     *
     * @param array $topics
     * @return array
     */
    protected function _getTopicsFlatList(array $topics)
    {
        $flatList = array();

        foreach ($topics as $topicGroup => $topicNames) {
            $topicNamesKeys = array_keys($topicNames);
            foreach ($topicNamesKeys as $topicName) {
                $flatList[] = $topicGroup . '/' . $topicName;
            }
        }

        return $flatList;
    }
}
