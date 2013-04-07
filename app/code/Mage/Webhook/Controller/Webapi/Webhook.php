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

class Mage_Webhook_Controller_Webapi_Webhook extends Mage_Webapi_Controller_ActionAbstract
{

    private $_api_user_id;
/*
    public function __construct(Mage_Webapi_Controller_RequestAbstract $request,
        Mage_Webapi_Controller_Response $response, Mage_Core_Helper_Abstract $translationHelper = null
    ) {
        $translationHelper = $translationHelper ? $translationHelper : Mage::helper('Mage_Webhook_Helper_Data');
        parent::__construct($request, $response, $translationHelper);
    }
*/

/**
     * Initialize dependencies.
     *
     * @param Mage_Webapi_Controller_Request_Factory $requestFactory
     * @param Mage_Webapi_Controller_Response_Factory $responseFactory
     * @param Mage_Core_Model_Factory_Helper $helperFactory
     * @param Magento_ObjectManager $objectManager
     */
    public function __construct(
        Mage_Webapi_Controller_Request_Factory $requestFactory,
        Mage_Webapi_Controller_Response_Factory $responseFactory,
        Mage_Core_Model_Factory_Helper $helperFactory,
        Magento_ObjectManager $objectManager
    ) {
        parent::__construct($requestFactory, $responseFactory, $helperFactory);
        $this->_translationHelper = $this->_helperFactory->get('Mage_Customer_Helper_Data');
        $this->_objectManager = $objectManager;
    }

    /**
     * Create webhook.
     *
     * @param Mage_Webhook_Model_Webapi_WebhookData $data webhook create data.
     * @param string $optional {maxLength:255 chars.}May be not passed.
     * @param int $int {min:10}{max:100} Optional integer parameter.
     * @param bool $bool optional boolean
     * @return int ID of registered subscriber
     * @throws Mage_Webapi_Exception
     */
    public function createV1(Mage_Webhook_Model_Webapi_WebhookData $data, $optional = 'default', $int = null, $bool = true)
    {
        try {
            $userId = $this->_getApiUserId();
            $topics = $this->_filterTopicsByApiUser($data->topics, $userId);

            /** @var $subscriber Mage_Webhook_Model_Subscriber */
            $subscriber = Mage::getModel('Mage_Webhook_Model_Subscriber');
            if (!empty($topics)) {
                $subscriber->setTopics($topics);
                $subscriber->setStatus(Mage_Webhook_Model_Subscriber::STATUS_ACTIVE);
            } else {
                $subscriber->setStatus(Mage_Webhook_Model_Subscriber::STATUS_INACTIVE);
            }
            $subscriber->setData(get_object_vars($data));
            $subscriber->setApiUserId($userId);

            $subscriber->save();

            $this->getResponse()->setHttpResponseCode(Mage_Webapi_Controller_Response_Rest::HTTP_CREATED);
        } catch (Mage_Core_Exception $e) {
            $this->_processException($e);
        }

        return $subscriber;
    }

    /**
     * Filter topics by the read permission of the api user
     *
     * @param $topics
     * @param $userId
     * @return array
     */
    private function _filterTopicsByApiUser($topics, $userId)
    {
        /** @var $user Mage_Webapi_Model_Acl_User */
        $user = Mage::getModel('Mage_Webapi_Model_Acl_User')->load($userId);
        $roleId = $user->getRoleId();
//        $resourceData = Mage::getResourceModel('Mage_Webapi_Model_Resource_Acl_Rule_Collection')
//            ->addFieldToFilter('role_id', $roleId)->load()->toArray(array('resource_id'));
//        $resourceData = isset($resourceData['items']) ? $resourceData['items'] : $resourceData;
        $resourceIds = Mage::getResourceModel('Mage_Webapi_Model_Resource_Acl_Rule')
            ->getResourceIdsByRole($roleId);
        if (in_array('Mage_Webapi', $resourceIds)) {    // all webapi permission
            return $topics;
        } else {
            $readableResources = array();
            foreach ($resourceIds as $resource) {
                if (preg_match("/\/get/", $resource)) { // TODO: Should be allowed to be anything, not just get
                    $result = preg_split("/\//", $resource);
                    $readableResources[] = $result[0];
                }
            }

            $resultTopics = array();
            foreach($topics as $topic) {
                $topic = str_replace("\/", "/", $topic);
                $array = preg_split("/\//", $topic);
                if (in_array($array[0], $readableResources)) {
                    $resultTopics[] = $topic;
                }
            }
            return $resultTopics;
        }
    }

    /**
     * Return Api User Id
     *
     * @return mixed
     */
    private function _getApiUserId()
    {
        if (!isset($this->_api_user_id)) {
            $consumerKey = null;
            $authHeader = $this->getRequest()->getHeader('Authorization');
            if (strpos($authHeader, 'OAuth ') !== false) {
                $authHeader = str_replace('OAuth ', '', $authHeader);
            }
            $authSettings = explode(',', $authHeader);
            foreach($authSettings as $authSetting) {
                list($key, $value) = explode('=', $authSetting);
                if (trim($key) === 'oauth_consumer_key'){
                    $consumerKey = str_replace(array('"'), "", trim($value));
                    break;
                }
            }
            $usersData = Mage::getModel('Mage_Webapi_Model_Acl_User')
                ->getCollection()
                ->addFieldToFilter('api_key', $consumerKey)
                ->load()
                ->toArray(array('user_id'));
            $users = isset($usersData['items']) ? $usersData['items'] : $usersData;
            $this->_setApiUserId($users[0]['user_id']);
        }
        return $this->_api_user_id;
    }

    private function _setApiUserId($id)
    {
        $this->_api_user_id = $id;
    }

    /**
     * Get WebHook list.
     *
     * @return Mage_Webhook_Model_Webapi_WebhookData[] array of WebHook data objects
     */
    public function listV1()
    {
        $result = array();
        $subscribersData = $this->_getSubscriberCollectionForRetrieve()
            ->load()
            ->toArray(Mage_Webhook_Model_Webapi_WebhookData::getSubscriberDataFields());
        $subscribersData = isset($subscribersData['items']) ? $subscribersData['items'] : $subscribersData;
        foreach ($subscribersData as $subscriberData) {
            $result[] = $this->_createWebhookDataObject($subscriberData);
        }
        return $result;
    }

    /**
     * Update webhook.
     *
     * @param int $id
     * @param string[][] $data
     * @throws Mage_Webapi_Exception
     */
    public function updateV1($id, $data)
    {
        try {
            /** @var $subscriber Mage_Webhook_Model_Subscriber */
            $subscriber = $this->_loadSubscriberById($id);
            foreach($data as $key => $value) {
                if ($key === 'topics') {
                    $userId = $this->_getApiUserId();
                    $topics = $this->_filterTopicsByApiUser($value, $userId);

                    if (empty($topics)) {
                        throw Mage::exception('Mage_Webhook', "The topics are not authorized.");
                    }
                    $subscriber->setTopics($topics);
                } else {
                    $subscriber->setData($key, $value);
                }

            }
            $subscriber->validate();
            $subscriber->save();
        } catch (Mage_Core_Exception $e) {
            $this->_processException($e);
        }

        return $subscriber;
    }

    /**
     * Retrieve WebHooks that a subscriber is registered to and also the subscriber information
     *
     * @param int $subscriberId
     * @return Mage_Webhook_Model_Webapi_WebhookData
     * @throws Mage_Webapi_Exception
     */
    public function getV1($subscriberId)
    {
        try {
            $subscriberData = $this->_get($subscriberId);
            return $this->_createWebhookDataObject($subscriberData);
        } catch (Mage_Core_Exception $e) {
            $this->_processException($e);
        }
    }

    /**
     * Create WebHook data object based on associative array of subscriber data.
     *
     * @param array $subscriberData
     * @return Mage_Webhook_Model_Webapi_WebhookData
     */
    protected function _createWebhookDataObject($subscriberData)
    {
        $webhookData = new Mage_Webhook_Model_Webapi_WebhookData();
        $subscriberId = $subscriberData['subscriber_id'];

        /** @var Mage_Webhook_Model_Subscriber $subscriber  */
        $subscriber = Mage::getModel('Mage_Webhook_Model_Subscriber');
        $subscriber->load($subscriberId);
        $webhookData->topics = $subscriber->getTopics();

        foreach ($subscriberData as $field => $value) {
            if (!empty($value)) {
                if ($field === 'status') {
                    $value = Mage_Webhook_Model_Webapi_WebhookData::getSubscriberStatusString($value);
                }
                $webhookData->$field = $value;
            }
        }
        return $webhookData;
    }

    /**
     * Retrieve subscriber data by ID
     *
     * @param string $id
     * @return array
     */
    protected function _get($id)
    {
        $collection = $this->_getSubscriberCollectionForRetrieve()
            ->addFieldToFilter('subscriber_id', $id);

        $subscribersData = $collection->load()
            ->toArray(Mage_Webhook_Model_Webapi_WebhookData::getSubscriberDataFields());
        $subscribers = isset($subscribersData['items']) ? $subscribersData['items'] : $subscribersData;
        $count = count($subscribers);
        if ($count > 1) {
            throw new Mage_Webapi_Exception(
                $this->_translationHelper->__("There are more than one subscriber with id %s.", $id),
                Mage_Webapi_Exception::HTTP_BAD_REQUEST
            );
        }
        if ($count == 0) {
            throw new Mage_Webapi_Exception(
                $this->_translationHelper->__("WebHook with id %s does not exist.", $id),
                Mage_Webapi_Exception::HTTP_NOT_FOUND
            );
        }
        return $subscribers[0];
    }

    /**
     * Delete webhook.
     *
     * @param string $id
     * @throws Mage_Webapi_Exception
     */
    public function deleteV1($id)
    {
        try {
            /** @var $subscriber Mage_Webhook_Model_Subscriber */
            $subscriber = $this->_loadSubscriberById($id);
            $subscriber->delete();
        } catch (Mage_Core_Exception $e) {
            $this->_processException($e);
        }
    }

    /**
     * Load subscriber by id.
     *
     * @param int $id
     * @return Mage_Webhook_Model_Subscriber
     * @throws Mage_Webapi_Exception
     */
    protected function _loadSubscriberById($id)
    {
        /** @var $subscriber Mage_Webhook_Model_Subscriber */
        $subscriber = Mage::getModel('Mage_Webhook_Model_Subscriber')->load($id);
        if (!$subscriber->getId() || $subscriber->getApiUserId() != $this->_getApiUserId()) {
            throw new Mage_Webapi_Exception(
                $this->_translationHelper->__("WebHook with id %s does not exist.", $id),
                Mage_Webapi_Exception::HTTP_NOT_FOUND
            );
        }
        return $subscriber;
    }

    /**
     * Retrieve subscriber collection instances
     *
     * @return Mage_Webhook_Model_Resource_Subscriber_Collection
     */
    protected function _getSubscriberCollectionForRetrieve()
    {
        /** @var $collection Mage_Webhook_Model_Resource_Subscriber_Collection */
        $collection = Mage::getResourceModel('Mage_Webhook_Model_Resource_Subscriber_Collection')
            ->addFieldToFilter('api_user_id', $this->_getApiUserId());
        $this->_applyCollectionModifiers($collection);
        return $collection;
    }

    /**
     * Process models exceptions and convert them into Webapi bad request exception.
     *
     * @param Exception $e
     * @throws Mage_Webapi_Exception
     */
    protected function _processException($e)
    {
        throw new Mage_Webapi_Exception($e->getMessage(), Mage_Webapi_Exception::HTTP_BAD_REQUEST);
    }
}
