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
 * @category    Magento
 * @package     Magento_PubSub
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\PubSub;

interface SubscriptionInterface extends \Magento\Outbound\EndpointInterface
{

    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_REVOKED = 2;

    /**
     * Returns a list of topics that this Subscription is subscribed to
     *
     * @return string[]
     */
    public function getTopics();

    /**
     * Determines if the subscription is subscribed to a topic.
     *
     * @param string $topic     The topic to check
     * @return boolean          True if subscribed, false otherwise
     */
    public function hasTopic($topic);


    /**
     * Get the status of this endpoint
     *
     * @return int Should match one of the status constants in \Magento\PubSub\SubscriptionInterface
     */
    public function getStatus();

    /**
     * Mark this subscription status as deactivated
     *
     * @return \Magento\PubSub\SubscriptionInterface The deactivated subscription
     */
    public function deactivate();


    /**
     * Mark this subscription status to activated
     *
     * @return \Magento\PubSub\SubscriptionInterface The activated subscription
     */
    public function activate();


    /**
     * Mark this subscription status to revoked
     *
     * @return \Magento\PubSub\SubscriptionInterface The revoked subscription
     */
    public function revoke();

    /**
     * Return endpoint with the subscription
     *
     * @return \Magento\Outbound\EndpointInterface
     */
    public function getEndpoint();
}
