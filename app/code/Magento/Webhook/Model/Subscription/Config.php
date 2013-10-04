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
 * @category    Magento
 * @package     Magento_Webhook
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Webhook\Model\Subscription;

class Config
{
    /** Webhook subscription configuration path */
    const XML_PATH_SUBSCRIPTIONS = 'global/webhook/subscriptions';

    /** @var \Magento\Webhook\Model\Resource\Subscription\Collection  */
    protected $_subscriptionSet;

    /** @var  \Magento\Core\Model\Config */
    protected $_mageConfig;

    /** @var  \Magento\Webhook\Model\Subscription\Factory */
    protected $_subscriptionFactory;

    /** @var \Magento\Core\Model\Logger */
    private $_logger;

    /**
     * @param \Magento\Webhook\Model\Resource\Subscription\Collection $subscriptionSet
     * @param \Magento\Core\Model\Config $mageConfig
     * @param \Magento\Webhook\Model\Subscription\Factory $subscriptionFactory
     * @param \Magento\Core\Model\Logger $logger
     */
    public function __construct(
        \Magento\Webhook\Model\Resource\Subscription\Collection $subscriptionSet,
        \Magento\Core\Model\Config $mageConfig,
        \Magento\Webhook\Model\Subscription\Factory $subscriptionFactory,
        \Magento\Core\Model\Logger $logger
    ) {
        $this->_subscriptionSet = $subscriptionSet;
        $this->_mageConfig = $mageConfig;
        $this->_subscriptionFactory = $subscriptionFactory;
        $this->_logger = $logger;
    }

    /**
     * Checks if new subscriptions need to be generated from config files
     *
     * @return \Magento\Webhook\Model\Subscription\Config
     */
    public function updateSubscriptionCollection()
    {
        $subscriptionConfig = $this->_mageConfig->getNode(self::XML_PATH_SUBSCRIPTIONS);

        if (!empty($subscriptionConfig)) {
            $subscriptionConfig = $subscriptionConfig->asArray();
        }
        // It could be no subscriptions have been defined
        if (!$subscriptionConfig) {
            return $this;
        }

        foreach ($subscriptionConfig as $alias => $subscriptionData) {
            try {
                $this->_validateConfigData($subscriptionData, $alias);
                $subscriptions = $this->_subscriptionSet->getSubscriptionsByAlias($alias);
                if (empty($subscriptions)) {
                    // add new subscription
                    $subscription = $this->_subscriptionFactory->create()
                        ->setAlias($alias)
                        ->setStatus(\Magento\Webhook\Model\Subscription::STATUS_INACTIVE);
                } else {
                    // get first subscription from array
                    $subscription = current($subscriptions);
                }

                // update subscription from config
                $this->_updateSubscriptionFromConfigData($subscription, $subscriptionData);
            } catch (\LogicException $e){
                $this->_logger->logException(new \Magento\Webhook\Exception($e->getMessage()));
            }
        }
        return $this;
    }

    /**
     * Validates config data by checking that $data is an array and that 'data' maps to some value
     *
     * @param mixed $data
     * @param string $alias
     * @throws \LogicException
     */
    protected function _validateConfigData($data, $alias)
    {
        //  We can't demand that every possible value be supplied as some of these can be supplied
        //  at a later point in time using the web API
        if (!( is_array($data) && isset($data['name']))) {
            throw new \LogicException(__(
                "Invalid config data for subscription '%1'.", $alias
            ));
        }
    }

    /**
     * Configures a subscription
     *
     * @param \Magento\Webhook\Model\Subscription $subscription
     * @param array $rawConfigData
     * @return \Magento\Core\Model\AbstractModel
     */
    protected function _updateSubscriptionFromConfigData(
        \Magento\Webhook\Model\Subscription $subscription,
        array $rawConfigData
    ) {
        // Set defaults for unset values
        $configData = $this->_processConfigData($rawConfigData);

        $subscription->setName($configData['name'])
            ->setFormat($configData['format'])
            ->setEndpointUrl($configData['endpoint_url'])
            ->setTopics($configData['topics'])
            ->setAuthenticationType($configData['authentication_type'])
            ->setRegistrationMechanism($configData['registration_mechanism']);

        return $subscription->save();
    }

    /**
     * Sets defaults for unset values
     *
     * @param array $configData
     * @return array
     */
    private function _processConfigData($configData)
    {
        $defaultData = array(
            'name' => null,
            'format' => \Magento\Outbound\EndpointInterface::FORMAT_JSON,
            'endpoint_url' => null,
            'topics' => array(),
            'authentication_type' => \Magento\Outbound\EndpointInterface::AUTH_TYPE_NONE,
            'registration_mechanism' => \Magento\Webhook\Model\Subscription::REGISTRATION_MECHANISM_MANUAL,
        );

        if (isset($configData['topics'])) {
            $configData['topics'] = $this->_getTopicsFlatList($configData['topics']);
        }

        return array_merge($defaultData, $configData);
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
