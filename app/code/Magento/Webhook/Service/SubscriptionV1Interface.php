<?php
/**
 * Webhook Subscription Service - Version 1.
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

interface SubscriptionV1Interface
{

    /**
     * Create a new Subscription
     *
     * @param array $subscriptionData
     * @return array Subscription data
     * @throws \Exception|\Magento\Core\Exception
     * @throws \Magento\Webhook\Exception
     */
    public function create(array $subscriptionData);

    /**
     * Get all Subscriptions associated with a given api user.
     *
     * @param int $apiUserId
     * @return array of Subscription data arrays
     * @throws \Exception|\Magento\Core\Exception
     * @throws \Magento\Webhook\Exception
     */
    public function getAll($apiUserId);

    /**
     * Update a Subscription.
     *
     * @param array $subscriptionData
     * @return array Subscription data
     * @throws \Exception|\Magento\Core\Exception
     * @throws \Magento\Webhook\Exception
     */
    public function update(array $subscriptionData);

    /**
     * Get the details of a specific Subscription.
     *
     * @param int $subscriptionId
     * @return array Subscription data
     * @throws \Exception|\Magento\Core\Exception
     * @throws \Magento\Webhook\Exception
     */
    public function get($subscriptionId);

    /**
     * Delete a Subscription.
     *
     * @param int $subscriptionId
     * @return array Subscription data
     * @throws \Exception|\Magento\Core\Exception
     * @throws \Magento\Webhook\Exception
     */
    public function delete($subscriptionId);

    /**
     * Activate a subscription.
     *
     * @param int $subscriptionId
     * @return array
     * @throws \Exception|\Magento\Core\Exception
     * @throws \Magento\Webhook\Exception
     */
    public function activate($subscriptionId);

    /**
     * De-activate a subscription.
     *
     * @param int $subscriptionId
     * @return array
     * @throws \Exception|\Magento\Core\Exception
     * @throws \Magento\Webhook\Exception
     */
    public function deactivate($subscriptionId);

    /**
     * Revoke a subscription.
     *
     * @param int $subscriptionId
     * @return array
     * @throws \Exception|\Magento\Core\Exception
     * @throws \Magento\Webhook\Exception
     */
    public function revoke($subscriptionId);

}
