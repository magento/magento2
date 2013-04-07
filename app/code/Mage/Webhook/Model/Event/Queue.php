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

class Mage_Webhook_Model_Event_Queue implements Mage_Webhook_Model_Event_Queue_Interface
{
    protected $_eventsIterator = null;

    public function offer(Mage_Webhook_Model_Event_Interface $event)
    {
        if ($event instanceof Mage_Webhook_Model_Event) {
            $event->setStatus(Mage_Webhook_Model_Event::READY_TO_SEND);
            $event->save();
            return true;
        }

        return false;
    }

    public function poll()
    {
        $iterator = $this->_getIterator();
        if ($iterator->valid()) {
            $event = $iterator->current();

            // effectively remove it from the queue
            $event->setStatus(Mage_Webhook_Model_Event::PROCESSED);
            $event->save();

            $iterator->next();
            return $event;
        }

        return null;
    }

    protected function _getIterator()
    {
        if (is_null($this->_eventsIterator) || !$this->_eventsIterator->valid()) {
            $collection = $this->_getEventCollection()
                ->addFieldToFilter('status', Mage_Webhook_Model_Event::READY_TO_SEND);
            $this->_eventsIterator = $collection->getIterator();
        }

        return $this->_eventsIterator;
    }

    protected function _getEventCollection() {
        return $collection = Mage::getModel('Mage_Webhook_Model_Event')->getCollection();
    }
}
