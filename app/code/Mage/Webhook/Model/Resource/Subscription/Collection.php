<?php
/**
 * Subscription collection resource
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
class Mage_Webhook_Model_Resource_Subscription_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
    implements Magento_PubSub_Subscription_CollectionInterface
{
    /**
     * @var Mage_Webhook_Model_Resource_Endpoint
     */
    protected $_endpointResource;

    /**
     * Collection constructor
     *
     * @param Varien_Data_Collection_Db_FetchStrategyInterface $fetchStrategy
     * @param Mage_Webhook_Model_Resource_Endpoint $endpointResource
     * @param Mage_Core_Model_Resource_Db_Abstract $resource
     */
    public function __construct(
        Varien_Data_Collection_Db_FetchStrategyInterface $fetchStrategy,
        Mage_Webhook_Model_Resource_Endpoint $endpointResource,
        Mage_Core_Model_Resource_Db_Abstract $resource = null
    ) {
        parent::__construct($fetchStrategy, $resource);
        $this->_endpointResource = $endpointResource;
    }

    /**
     * Initialization here
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('Mage_Webhook_Model_Subscription', 'Mage_Webhook_Model_Resource_Subscription');
    }


    /**
     * Return all subscriptions by topic
     *
     * @param string $topic
     * @return Magento_PubSub_SubscriptionInterface[]
     */
    public function getSubscriptionsByTopic($topic)
    {
        return $this->clearFilters()
            ->addTopicFilter($topic)
            ->addIsActiveFilter(true)
            ->clear()
            ->getItems();
    }

    /**
     * Returns all subscriptions that match a given alias
     *
     * @param string $alias
     * @return Mage_Webhook_Model_Subscription[]
     */
    public function getSubscriptionsByAlias($alias)
    {
        return $this->clearFilters()
            ->addAliasFilter($alias)
            ->clear()
            ->getItems();
    }

    /**
     * Get subscriptions whose endpoint has no api user
     *
     * @return Mage_Webhook_Model_Subscription[]
     */
    public function getActivatedSubscriptionsWithoutApiUser()
    {
        $endpointIds = $this->_endpointResource->getEndpointsWithoutApiUser();

        return $this->clearFilters()
            ->addEndpointIdsFilter($endpointIds)
            ->addNotInactiveFilter()
            ->clear()
            ->getItems();
    }

    /**
     * Get api user subscriptions
     *
     * @param int|int[] $apiUserIds
     * @return Mage_Webhook_Model_Subscription[]
     */
    public function getApiUserSubscriptions($apiUserIds)
    {
        $endpointIds = $this->_endpointResource->getApiUserEndpoints($apiUserIds);

        return $this->clearFilters()
            ->addEndpointIdsFilter($endpointIds)
            ->clear()
            ->getItems();
    }

    /**
     * Clear the select object
     *
     * @return Mage_Webhook_Model_Resource_Subscription_Collection
     */
    public function clearFilters()
    {
        $this->_select = $this->_conn->select();
        $this->_initSelect();
        return $this;
    }

    /**
     * Select subscriptions whose endpoint's id is in given array
     *
     * @param array $endpointIds
     * @return Mage_Webhook_Model_Resource_Subscription_Collection
     */
    public function addEndpointIdsFilter($endpointIds)
    {
        $this->getSelect()->where('endpoint_id IN (?)', $endpointIds);

        return $this;
    }

    /**
     * Add filter by topic field to collection
     *
     * @param string $topic
     * @return Mage_Webhook_Model_Resource_Subscription_Collection
     */
    public function addTopicFilter($topic)
    {
        $this->getSelect()
            ->joinInner(array('hooks' => $this->getTable('webhook_subscription_hook')),
                $this->getConnection()
                    ->quoteInto('hooks.subscription_id=main_table.subscription_id AND hooks.topic=?', $topic)
        );
        return $this;
    }

    /**
     * Add filter by alias field to collection
     * 
     * @param string|array $alias
     * @return Mage_Webhook_Model_Resource_Subscription_Collection
     */
    public function addAliasFilter($alias)
    {
        $this->addFieldToFilter('alias', $alias);
        return $this;
    }

    /**
     * Adds filter by status field to collection based on parameter
     *
     * @param bool $isActive
     * @return Mage_Webhook_Model_Resource_Subscription_Collection
     */
    public function addIsActiveFilter($isActive)
    {
        if ($isActive) {
            $this->addFieldToFilter('status', Magento_PubSub_SubscriptionInterface::STATUS_ACTIVE);
        } else {
            $this->addFieldToFilter('status', Magento_PubSub_SubscriptionInterface::STATUS_INACTIVE);
        }
        return $this;
    }

    /**
     * Filter out anything in the INACTIVE state
     *
     * @return Mage_Webhook_Model_Resource_Subscription_Collection
     */
    public function addNotInactiveFilter()
    {
        $this->getSelect()->where('status IN (?)', array(
            Mage_Webhook_Model_Subscription::STATUS_ACTIVE,
            Mage_Webhook_Model_Subscription::STATUS_REVOKED));

        return $this;
    }
}
