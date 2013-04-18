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

class Mage_Webhook_Model_Subscriber_Config
{
    const XML_PATH_SUBSCRIBERS = 'global/webhook/subscribers';

    /**
     * TODO: We only call this from _prepareCollection in Grid.php.  We should probably call this whenever a new
     *       Magento extension is installed or updated.
     */
    public function updateSubscriberCollection()
    {
        $subscriberConfig = $this->_getSubscriberConfigNode();

        if (!empty($subscriberConfig)) {
            $subscriberConfig = $subscriberConfig->asArray();
        }
        $subscriberCollection = $this->_getSubscriberCollection();

        $errors = array();

        foreach ($subscriberConfig as $extentionId => $subscriberData) {
            if (!$this->_validateConfigData($subscriberData)) {
                $errors[] = Mage::helper('Mage_Webhook_Helper_Data')
                    ->__("Invalid config data for subscriber '%s'.", $extentionId);
                continue;
            }

            $subscribers = $subscriberCollection->getItemsByColumnValue('extension_id', $extentionId);
            if (empty($subscribers)) {
                // add new subscriber
                $this->_addSubscriberFromConfigData($extentionId, $subscriberData);
                continue;
            } else {
                // get first subscriber from array
                $subscriber = current($subscribers);
            }

            if (isset($subscriberData['version']) && $subscriber->getVersion() != $subscriberData['version']) {
                // update subscriber from config
                $this->_updateSubscriberFromConfigData($subscriber, $subscriberData);
            }
        }

        if (!empty($errors)) {
            $this->_handleErrors($errors);
        }

        return $this;
    }

    /**
     * If we have errors, lets log them, but lets not blow up as there could be some valid configurations
     */
    protected function _handleErrors($errors)
    {
        Mage::logException(Mage::exception('Mage_Webhook', implode("\n", $errors)));
    }

    protected function _getSubscriberConfigNode()
    {
        $subscriberConfigNode = Mage::getConfig()->getNode(self::XML_PATH_SUBSCRIBERS);
        return $subscriberConfigNode;
    }

    protected function _validateConfigData($data)
    {
        //  We can't demand that every possible value be supplied as some of these can be supplied
        //  at a later point in time using the web API
        return is_array($data) && isset($data['name']);
    }

    protected function _addSubscriberFromConfigData($extentionId, array $configData)
    {
        /** @var $subscriber Mage_Webhook_Model_Subscriber */
        $subscriber = $this->_createSubscriber($extentionId);
        return $this->_updateSubscriberFromConfigData($subscriber, $configData);
    }

    protected function _createSubscriber($extentionId)
    {
        $subscriber = Mage::getModel('Mage_Webhook_Model_Subscriber')
            ->setExtensionId($extentionId)
            ->setStatus(Mage_Webhook_Model_Subscriber::STATUS_INACTIVE);
        return $subscriber;
    }

    protected function _updateSubscriberFromConfigData(Mage_Webhook_Model_Subscriber $subscriber, array $configData)
    {
        $subscriber->setName($configData['name'])
            ->setMapping($this->_get($configData, 'mapping', Mage_Webhook_Model_Subscriber::MAPPING_DEFAULT))
            ->setFormat($this->_get($configData, 'format', Mage_Webhook_Model_Subscriber::FORMAT_JSON))
            ->setVersion($this->_get($configData, 'version'))
            ->setEndpointUrl($this->_get($configData, 'endpoint_url'))
            ->setTopics(isset($configData['topics']) ? $this->_getTopicsFlatList($configData['topics']) : array())
            ->setAuthenticationType(
                isset($configData['authentication']['type'])
                ? $configData['authentication']['type']
                : Mage_Webhook_Model_Subscriber::AUTH_TYPE_NONE
            )
            ->setRegistrationMechanism(
                isset($configData['registration_mechanism'])
                ? $configData['registration_mechanism']
                : Mage_Webhook_Model_Subscriber::REGISTRATION_MECHANISM_MANUAL
            );

        return $subscriber->save();
    }

    private function _get($array, $key, $default=null)
    {
        if (isset($array[$key])) {
            return $array[$key];
        } else {
            return $default;
        }
    }

    protected function _getTopicsFlatList(array $topics)
    {
        $flatList = array();

        foreach ($topics as $topicGroup => $topicNames) {
            $topicNamesList = array_keys($topicNames);
            foreach ($topicNamesList as $topicName) {
                $flatList[] = $topicGroup . '/' . $topicName;
            }
        }

        return $flatList;
    }

    protected function _getSubscriberCollection()
    {
        return Mage::getResourceModel('Mage_Webhook_Model_Resource_Subscriber_Collection');
    }
}
