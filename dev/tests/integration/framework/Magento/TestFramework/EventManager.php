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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Basic implementation of the message exchange mechanism known as pub/sub messaging pattern.
 * Terminology deviations:
 *   event            - message
 *   event manager    - message publisher
 *   fire event       - publish message
 *   event subscriber - message subscriber
 */
namespace Magento\TestFramework;

class EventManager
{
    /**
     * Registered event subscribers
     *
     * @var array
     */
    protected $_subscribers = array();

    /**
     * Constructor
     *
     * @param array $subscribers Subscriber instances
     */
    public function __construct(array $subscribers)
    {
        $this->_subscribers = $subscribers;
    }

    /**
     * Notify registered subscribers, which are interested in event
     *
     * @param string $eventName
     * @param array $parameters Parameters to be passed to each subscriber
     * @param bool $reverseOrder Whether subscribers should be notified in reverse order
     */
    public function fireEvent($eventName, array $parameters = array(), $reverseOrder = false)
    {
        $subscribers = $reverseOrder ? array_reverse($this->_subscribers) : $this->_subscribers;
        foreach ($subscribers as $subscriberInstance) {
            $callback = array($subscriberInstance, $eventName);
            if (is_callable($callback)) {
                call_user_func_array($callback, $parameters);
            }
        }
    }
}
