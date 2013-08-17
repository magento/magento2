<?php
/**
 * Webhook Subscription Service.
 *
 * This service is used to interact with webhooks subscriptions.
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
class Mage_Webhook_Service_SubscriptionV1 implements Mage_Webhook_Service_SubscriptionV1Interface
{
    /** @var Mage_Webhook_Model_Subscription_Factory $_subscriptionFactory */
    private $_subscriptionFactory;

    /** @var Mage_Webhook_Model_User_Factory $_whUserFactory */
    private $_whUserFactory;

    /** @var Mage_Webhook_Model_Resource_Subscription_Collection $_subscriptionSet */
    private $_subscriptionSet;

    /** @var Mage_Core_Model_Translate Mage_Core_Model_Translate */
    private $_translator;

    /**
     * @param Mage_Webhook_Model_Subscription_Factory $subscriptionFactory
     * @param Mage_Webhook_Model_User_Factory $whUserFactory
     * @param Mage_Webhook_Model_Resource_Subscription_Collection $subscriptionSet
     * @param Mage_Core_Model_Translate $translator
     */
    public function __construct(
        Mage_Webhook_Model_Subscription_Factory $subscriptionFactory,
        Mage_Webhook_Model_User_Factory $whUserFactory,
        Mage_Webhook_Model_Resource_Subscription_Collection $subscriptionSet,
        Mage_Core_Model_Translate $translator
    ) {
        $this->_subscriptionFactory = $subscriptionFactory;
        $this->_whUserFactory = $whUserFactory;
        $this->_subscriptionSet = $subscriptionSet;
        $this->_translator = $translator;
    }

    /**
     * Create a new Subscription
     *
     * @param array $subscriptionData
     * @return array Subscription data
     * @throws Exception|Mage_Core_Exception
     * @throws Mage_Webhook_Exception
     */
    public function create(array $subscriptionData)
    {
        try {
            $subscription = $this->_subscriptionFactory->create($subscriptionData);

            $this->_validateTopics($subscription);

            $subscription->save();

            return $subscription->getData();
        } catch (Mage_Core_Exception $exception) {
            // These messages are already translated, we can simply surface them.
            throw $exception;
        } catch (Exception $exception) {
            // These messages have no translation, we should not expose our internals but may consider logging them.
            throw new Mage_Webhook_Exception(
                $this->_translator->translate(array('Unexpected error.  Please contact the administrator.'))
            );
        }
    }

    /**
     * Get all Subscriptions associated with a given api user.
     *
     * @param int $apiUserId
     * @throws Exception|Mage_Core_Exception
     * @throws Mage_Webhook_Exception
     * @return array of Subscription data arrays
     */
    public function getAll($apiUserId)
    {
        try {
            $result = array();
            $subscriptions = $this->_subscriptionSet->getApiUserSubscriptions($apiUserId);

            /** @var Mage_Webhook_Model_Subscription $subscription*/
            foreach ($subscriptions as $subscription) {
                $result[] = $subscription->getData();
            }

            return $result;
        } catch (Mage_Core_Exception $e) {
            // These messages are already translated, we can simply surface them.
            throw $e;
        } catch (Exception $e) {
            // These messages have no translation, we should not expose our internals but may consider logging them.
            throw new Mage_Webhook_Exception(
                $this->_translator->translate(array('Unexpected error.  Please contact the administrator.'))
            );
        }
    }

    /**
     * Update a Subscription.
     *
     * @param array $subscriptionData
     * @return array Subscription data
     * @throws Exception|Mage_Core_Exception
     * @throws Mage_Webhook_Exception
     */
    public function update(array $subscriptionData)
    {
        try {
            $subscription = $this->_loadSubscriptionById($subscriptionData['subscription_id']);
            $subscription->addData($subscriptionData);

            $this->_validateTopics($subscription);

            $subscription->save();

            return $subscription->getData();
        } catch (Mage_Core_Exception $e) {
            // These messages are already translated, we can simply surface them.
            throw $e;
        } catch (Exception $e) {
            // These messages have no translation, we should not expose our internals but may consider logging them.
            throw new Mage_Webhook_Exception(
                $this->_translator->translate(array('Unexpected error.  Please contact the administrator.'))
            );
        }
    }

    /**
     * Get the details of a specific Subscription.
     *
     * @param int $subscriptionId
     * @return array Subscription data
     * @throws Exception|Mage_Core_Exception
     * @throws Mage_Webhook_Exception
     */
    public function get($subscriptionId)
    {
        try {
            $subscription = $this->_loadSubscriptionById($subscriptionId);
            return $subscription->getData();
        } catch (Mage_Core_Exception $e) {
            // These messages are already translated, we can simply surface them.
            throw $e;
        } catch (Exception $e) {
            // These messages have no translation, we should not expose our internals but may consider logging them.
            throw new Mage_Webhook_Exception(
                $this->_translator->translate(array('Unexpected error.  Please contact the administrator.'))
            );
        }
    }

    /**
     * Delete a Subscription.
     *
     * @param int $subscriptionId
     * @return array Subscription data
     * @throws Exception|Mage_Core_Exception
     * @throws Mage_Webhook_Exception
     */
    public function delete($subscriptionId)
    {
        try {
            $subscription = $this->_loadSubscriptionById($subscriptionId);
            $subscriptionData = $subscription->getData();

            $subscription->delete();

            return $subscriptionData;
        } catch (Mage_Core_Exception $e) {
            // These messages are already translated, we can simply surface them.
            throw $e;
        } catch (Exception $e) {
            // These messages have no translation, we should not expose our internals but may consider logging them.
            throw new Mage_Webhook_Exception(
                $this->_translator->translate(array('Unexpected error.  Please contact the administrator.'))
            );
        }
    }

    /**
     * Activate a subscription.
     *
     * @param int $subscriptionId
     * @return array
     * @throws Exception|Mage_Core_Exception
     * @throws Mage_Webhook_Exception
     */
    public function activate($subscriptionId)
    {
        try {
            $subscription = $this->_loadSubscriptionById($subscriptionId);

            $subscription->activate();
            $subscription->save();
            return $subscription->getData();
        } catch (Mage_Core_Exception $e) {
            // These messages are already translated, we can simply surface them.
            throw $e;
        } catch (Exception $e) {
            // These messages have no translation, we should not expose our internals but may consider logging them.
            throw new Mage_Webhook_Exception(
                $this->_translator->translate(array('Unexpected error.  Please contact the administrator.'))
            );
        }
    }

    /**
     * De-activate a subscription.
     *
     * @param int $subscriptionId
     * @return array
     * @throws Exception|Mage_Core_Exception
     * @throws Mage_Webhook_Exception
     */
    public function deactivate($subscriptionId)
    {
        try {
            $subscription = $this->_loadSubscriptionById($subscriptionId);

            $subscription->deactivate();
            $subscription->save();
            return $subscription->getData();
        } catch (Mage_Core_Exception $e) {
            // These messages are already translated, we can simply surface them.
            throw $e;
        } catch (Exception $e) {
            // These messages have no translation, we should not expose our internals but may consider logging them.
            throw new Mage_Webhook_Exception(
                $this->_translator->translate(array('Unexpected error.  Please contact the administrator.'))
            );
        }
    }

    /**
     * Revoke a subscription.
     *
     * @param int $subscriptionId
     * @return array
     * @throws Exception|Mage_Core_Exception
     * @throws Mage_Webhook_Exception
     */
    public function revoke($subscriptionId)
    {
        try {
            $subscription = $this->_loadSubscriptionById($subscriptionId);

            $subscription->revoke();
            $subscription->save();
            return $subscription->getData();
        } catch (Mage_Core_Exception $e) {
            // These messages are already translated, we can simply surface them.
            throw $e;
        } catch (Exception $e) {
            // These messages have no translation, we should not expose our internals but may consider logging them.
            throw new Mage_Webhook_Exception(
                $this->_translator->translate(array('Unexpected error.  Please contact the administrator.'))
            );
        }
    }

    /**
     * Returns trues if a given userId is associated with a subscription
     *
     * @param int $apiUserId
     * @param int $subscriptionId
     * @throws Mage_Webhook_Exception
     */
    public function validateOwnership($apiUserId, $subscriptionId)
    {
        $subscription = $this->_loadSubscriptionById($subscriptionId);
        if ($subscription->getApiUserId() != $apiUserId) {
            throw new Mage_Webhook_Exception(
                $this->_translator->translate(array(
                    "User with id %s doesn't have permission to modify subscription %s",
                    $apiUserId,
                    $subscriptionId,
                ))
            );
        }
    }

    /**
     * Validates all the topics for a Subscription are Authorized.
     *
     * If invalid topics exists, an exception will be thrown.
     *
     * @param Mage_Webhook_Model_Subscription $subscription
     * @throws Mage_Webhook_Exception
     */
    private function _validateTopics(Mage_Webhook_Model_Subscription $subscription)
    {
        $invalidTopics = $subscription->findRestrictedTopics();
        if (!empty($invalidTopics)) {
            $listOfTopics = implode(', ', $invalidTopics);
            throw new Mage_Webhook_Exception(
                $this->_translator->translate(array('The following topics are not authorized: %s', $listOfTopics))
            );
        }
    }

    /**
     * Load subscription by id.
     *
     * @param int $subscriptionId
     * @throws Mage_Webhook_Exception
     * @return Mage_Webhook_Model_Subscription
     */
    protected function _loadSubscriptionById($subscriptionId)
    {
        $subscription = $this->_subscriptionFactory->create()->load($subscriptionId);
        if (!$subscription->getId()) {
            throw new Mage_Webhook_Exception(
                $this->_translator->translate(array("Subscription with ID '%s' doesn't exist.", $subscriptionId))
            );
        }
        return $subscription;
    }

}
