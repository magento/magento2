<?php
/**
 * Fulfills event queueing functionality for Magento,
 * wrapper around Magento collection with Event QueueReader Interface.
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
class Mage_Webhook_Model_Event_QueueReader implements Magento_PubSub_Event_QueueReaderInterface
{
    /** @var Mage_Webhook_Model_Resource_Event_Collection */
    protected $_collection;

    /** @var ArrayIterator */
    protected $_iterator;

    /**
     * Initialize collection representing the queue
     *
     * @param Mage_Webhook_Model_Resource_Event_Collection $collection
     */
    public function __construct(Mage_Webhook_Model_Resource_Event_Collection $collection)
    {
        $this->_collection = $collection;
        $this->_collection->addFieldToFilter('status', Magento_PubSub_EventInterface::READY_TO_SEND);
        $this->_iterator = $this->_collection->getIterator();
    }

    /**
     * Get the top event from the queue.
     *
     * @return Magento_PubSub_EventInterface|null
     */
    public function poll()
    {
        if ($this->_iterator->valid()) {
            /** @var Mage_Webhook_Model_Event $event */
            $event = $this->_iterator->current();

            // effectively remove it from the queue
            $event->markAsProcessed();
            $event->save();

            $this->_iterator->next();
            return $event;
        }
        return null;
    }
}