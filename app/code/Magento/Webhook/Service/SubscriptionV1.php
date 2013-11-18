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
 * @category    Magento
 * @package     Magento_Webhook
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Webhook\Service;

class SubscriptionV1 implements \Magento\Webhook\Service\SubscriptionV1Interface
{
    /** @var \Magento\Webhook\Model\Subscription\Factory $_subscriptionFactory */
    private $_subscriptionFactory;

    /** @var \Magento\Webhook\Model\Resource\Subscription\Collection $_subscriptionSet */
    private $_subscriptionSet;

    /**
     * @param \Magento\Webhook\Model\Subscription\Factory $subscriptionFactory
     * @param \Magento\Webhook\Model\Resource\Subscription\Collection $subscriptionSet
     */
    public function __construct(
        \Magento\Webhook\Model\Subscription\Factory $subscriptionFactory,
        \Magento\Webhook\Model\Resource\Subscription\Collection $subscriptionSet
    ) {
        $this->_subscriptionFactory = $subscriptionFactory;
        $this->_subscriptionSet = $subscriptionSet;
    }

    /**
     * Create a new Subscription
     *
     * @param array $subscriptionData
     * @return array Subscription data
     * @throws \Exception|\Magento\Core\Exception
     * @throws \Magento\Webhook\Exception
     */
    public function create(array $subscriptionData)
    {
        try {
            $subscription = $this->_subscriptionFactory->create($subscriptionData);

            $this->_validateTopics($subscription);

            $subscription->save();

            return $subscription->getData();
        } catch (\Magento\Core\Exception $exception) {
            // These messages are already translated, we can simply surface them.
            throw $exception;
        } catch (\Exception $exception) {
            // These messages have no translation, we should not expose our internals but may consider logging them.
            throw new \Magento\Webhook\Exception(
                __('Unexpected error.  Please contact the administrator.')
            );
        }
    }

    /**
     * Get all Subscriptions associated with a given api user.
     *
     * @param int $apiUserId
     * @throws \Exception|\Magento\Core\Exception
     * @throws \Magento\Webhook\Exception
     * @return array of Subscription data arrays
     */
    public function getAll($apiUserId)
    {
        try {
            $result = array();
            $subscriptions = $this->_subscriptionSet->getApiUserSubscriptions($apiUserId);

            /** @var \Magento\Webhook\Model\Subscription $subscription*/
            foreach ($subscriptions as $subscription) {
                $result[] = $subscription->getData();
            }

            return $result;
        } catch (\Magento\Core\Exception $e) {
            // These messages are already translated, we can simply surface them.
            throw $e;
        } catch (\Exception $e) {
            // These messages have no translation, we should not expose our internals but may consider logging them.
            throw new \Magento\Webhook\Exception(
                __('Unexpected error.  Please contact the administrator.')
            );
        }
    }

    /**
     * Update a Subscription.
     *
     * @param array $subscriptionData
     * @return array Subscription data
     * @throws \Exception|\Magento\Core\Exception
     * @throws \Magento\Webhook\Exception
     */
    public function update(array $subscriptionData)
    {
        try {
            $subscription = $this->_loadSubscriptionById($subscriptionData['subscription_id']);
            $subscription->addData($subscriptionData);

            $this->_validateTopics($subscription);

            $subscription->save();

            return $subscription->getData();
        } catch (\Magento\Core\Exception $e) {
            // These messages are already translated, we can simply surface them.
            throw $e;
        } catch (\Exception $e) {
            // These messages have no translation, we should not expose our internals but may consider logging them.
            throw new \Magento\Webhook\Exception(
                __('Unexpected error.  Please contact the administrator.')
            );
        }
    }

    /**
     * Get the details of a specific Subscription.
     *
     * @param int $subscriptionId
     * @return array Subscription data
     * @throws \Exception|\Magento\Core\Exception
     * @throws \Magento\Webhook\Exception
     */
    public function get($subscriptionId)
    {
        try {
            $subscription = $this->_loadSubscriptionById($subscriptionId);
            return $subscription->getData();
        } catch (\Magento\Core\Exception $e) {
            // These messages are already translated, we can simply surface them.
            throw $e;
        } catch (\Exception $e) {
            // These messages have no translation, we should not expose our internals but may consider logging them.
            throw new \Magento\Webhook\Exception(
                __('Unexpected error.  Please contact the administrator.')
            );
        }
    }

    /**
     * Delete a Subscription.
     *
     * @param int $subscriptionId
     * @return array Subscription data
     * @throws \Exception|\Magento\Core\Exception
     * @throws \Magento\Webhook\Exception
     */
    public function delete($subscriptionId)
    {
        try {
            $subscription = $this->_loadSubscriptionById($subscriptionId);
            $subscriptionData = $subscription->getData();

            $subscription->delete();

            return $subscriptionData;
        } catch (\Magento\Core\Exception $e) {
            // These messages are already translated, we can simply surface them.
            throw $e;
        } catch (\Exception $e) {
            // These messages have no translation, we should not expose our internals but may consider logging them.
            throw new \Magento\Webhook\Exception(
                __('Unexpected error.  Please contact the administrator.')
            );
        }
    }

    /**
     * Activate a subscription.
     *
     * @param int $subscriptionId
     * @return array
     * @throws \Exception|\Magento\Core\Exception
     * @throws \Magento\Webhook\Exception
     */
    public function activate($subscriptionId)
    {
        try {
            $subscription = $this->_loadSubscriptionById($subscriptionId);

            $subscription->activate();
            $subscription->save();
            return $subscription->getData();
        } catch (\Magento\Core\Exception $e) {
            // These messages are already translated, we can simply surface them.
            throw $e;
        } catch (\Exception $e) {
            // These messages have no translation, we should not expose our internals but may consider logging them.
            throw new \Magento\Webhook\Exception(
                __('Unexpected error.  Please contact the administrator.')
            );
        }
    }

    /**
     * De-activate a subscription.
     *
     * @param int $subscriptionId
     * @return array
     * @throws \Exception|\Magento\Core\Exception
     * @throws \Magento\Webhook\Exception
     */
    public function deactivate($subscriptionId)
    {
        try {
            $subscription = $this->_loadSubscriptionById($subscriptionId);

            $subscription->deactivate();
            $subscription->save();
            return $subscription->getData();
        } catch (\Magento\Core\Exception $e) {
            // These messages are already translated, we can simply surface them.
            throw $e;
        } catch (\Exception $e) {
            // These messages have no translation, we should not expose our internals but may consider logging them.
            throw new \Magento\Webhook\Exception(
                __('Unexpected error.  Please contact the administrator.')
            );
        }
    }

    /**
     * Revoke a subscription.
     *
     * @param int $subscriptionId
     * @return array
     * @throws \Exception|\Magento\Core\Exception
     * @throws \Magento\Webhook\Exception
     */
    public function revoke($subscriptionId)
    {
        try {
            $subscription = $this->_loadSubscriptionById($subscriptionId);

            $subscription->revoke();
            $subscription->save();
            return $subscription->getData();
        } catch (\Magento\Core\Exception $e) {
            // These messages are already translated, we can simply surface them.
            throw $e;
        } catch (\Exception $e) {
            // These messages have no translation, we should not expose our internals but may consider logging them.
            throw new \Magento\Webhook\Exception(
                __('Unexpected error.  Please contact the administrator.')
            );
        }
    }

    /**
     * Returns trues if a given userId is associated with a subscription
     *
     * @param int $apiUserId
     * @param int $subscriptionId
     * @throws \Magento\Webhook\Exception
     */
    public function validateOwnership($apiUserId, $subscriptionId)
    {
        $subscription = $this->_loadSubscriptionById($subscriptionId);
        if ($subscription->getApiUserId() != $apiUserId) {
            throw new \Magento\Webhook\Exception(
                __("User with id %1 doesn't have permission to modify subscription %2", $apiUserId, $subscriptionId)
            );
        }
    }

    /**
     * Validates all the topics for a Subscription are Authorized.
     *
     * If invalid topics exists, an exception will be thrown.
     *
     * @param \Magento\Webhook\Model\Subscription $subscription
     * @throws \Magento\Webhook\Exception
     */
    private function _validateTopics(\Magento\Webhook\Model\Subscription $subscription)
    {
        $invalidTopics = $subscription->findRestrictedTopics();
        if (!empty($invalidTopics)) {
            $listOfTopics = implode(', ', $invalidTopics);
            throw new \Magento\Webhook\Exception(
                __('The following topics are not authorized: %1', $listOfTopics)
            );
        }
    }

    /**
     * Load subscription by id.
     *
     * @param int $subscriptionId
     * @throws \Magento\Webhook\Exception
     * @return \Magento\Webhook\Model\Subscription
     */
    protected function _loadSubscriptionById($subscriptionId)
    {
        $subscription = $this->_subscriptionFactory->create()->load($subscriptionId);
        if (!$subscription->getId()) {
            throw new \Magento\Webhook\Exception(
                __("Subscription with ID '%1' doesn't exist.", $subscriptionId)
            );
        }
        return $subscription;
    }

}
