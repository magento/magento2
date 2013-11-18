<?php
/**
 * Fulfills event queueing functionality for Magento, writes events to database based queue
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
namespace Magento\Webhook\Model\Event;

class QueueWriter implements \Magento\PubSub\Event\QueueWriterInterface
{

    /** @var \Magento\Webhook\Model\Event\Factory */
    protected $_eventFactory;

    /**
     * Initialize queue writer
     */
    public function __construct(\Magento\Webhook\Model\Event\Factory $eventFactory)
    {
        $this->_eventFactory = $eventFactory;
    }

    /**
     * Adds event to the queue.
     *
     * @param \Magento\PubSub\EventInterface $event
     * @return null
     */
    public function offer(\Magento\PubSub\EventInterface $event)
    {
        if ($event instanceof \Magento\Webhook\Model\Event) {
            $event->save();
        } else {
            $magentoEvent = $this->_eventFactory->create($event->getTopic(), $event->getBodyData());
            $magentoEvent->save();
        }
    }
}
