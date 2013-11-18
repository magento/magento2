<?php
/**
 * Entry point to the dispatch event functionality for the cases in which the queueing is needed
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
namespace Magento\PubSub\Message;

class DispatcherAsync implements \Magento\PubSub\Message\DispatcherAsyncInterface
{
    /** @var \Magento\PubSub\Event\FactoryInterface */
    protected $_eventFactory;

    /** @var \Magento\PubSub\Event\QueueWriterInterface  */
    protected $_eventQueue;

    /**
     * @param \Magento\PubSub\Event\FactoryInterface $eventFactory
     * @param \Magento\PubSub\Event\QueueWriterInterface $eventQueue
     */
    public function __construct(
        \Magento\PubSub\Event\FactoryInterface $eventFactory,
        \Magento\PubSub\Event\QueueWriterInterface $eventQueue
    ) {
        $this->_eventFactory = $eventFactory;
        $this->_eventQueue = $eventQueue;
    }

    /**
     * Dispatch event with given topic and data
     *
     * @param string $topic
     * @param array $data should only contain primitives, no objects.
     */
    public function dispatch($topic, $data)
    {
        $event = $this->_eventFactory->create($topic, $data);
        $this->_eventQueue->offer($event);
    }
}
