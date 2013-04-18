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

class Mage_Webhook_Model_Resource_Subscriber_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('Mage_Webhook_Model_Subscriber', 'Mage_Webhook_Model_Resource_Subscriber');
    }

    /**
     * Add filter by topic field to collection
     *
     * @param $topic
     * @return Mage_Webhook_Model_Resource_Subscriber_Collection
     */
    public function addTopicFilter($topic)
    {
        $this->getSelect()
            ->joinInner(array('hooks' => $this->getTable('webhook_subscriber_hook')),
                $this->getConnection()
                    ->quoteInto('hooks.subscriber_id=main_table.subscriber_id AND hooks.topic=?', $topic)
        );
        return $this;
    }

    /**
     * Add filter by mapping field to collection
     *
     * @param $mapping
     * @return Mage_Webhook_Model_Resource_Subscriber_Collection
     */
    public function addMappingFilter($mapping)
    {
        $this->addFieldToFilter('mapping', $mapping);
        return $this;
    }

    public function addExtensionIdFilter($extensionId)
    {
        $this->addFieldToFilter('extension_id', $extensionId);
        return $this;
    }

    /**
     * Adds filter by status field to collection based on parameter
     *
     * @param $isActive
     * @return Mage_Webhook_Model_Resource_Subscriber_Collection
     */
    public function addIsActiveFilter($isActive)
    {
        if ($isActive) {
            $this->addFieldToFilter('status', Mage_Webhook_Model_Subscriber::STATUS_ACTIVE);
        } else {
            $this->addFieldToFilter('status', Mage_Webhook_Model_Subscriber::STATUS_INACTIVE);
        }
        return $this;
    }

    /**
     * Retrieve just a single subscriber
     *
     * @throws Mage_Core_Exception
     */
    public function getSingleSubscriber()
    {
        /** @var $subscriber Mage_Webhook_Model_Subscriber */
        $subscriber = $this->getFirstItem();
        if (!(bool)$subscriber->getId()) {
            return null;
        }
        return $subscriber;
    }
}
