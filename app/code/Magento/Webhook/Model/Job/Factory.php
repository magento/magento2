<?php
/**
 * Factory for \Magento\Webhook\Model\Job
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
namespace Magento\Webhook\Model\Job;

class Factory implements \Magento\PubSub\Job\FactoryInterface
{
    /**
     * @var \Magento\ObjectManager
     */
    protected $_objectManager;

    /**
     * Initialize the class
     *
     * @param \Magento\ObjectManager $objectManager
     */
    public function __construct(\Magento\ObjectManager $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create Job
     *
     * @param \Magento\PubSub\SubscriptionInterface $subscription
     * @param \Magento\PubSub\EventInterface $event
     * @return \Magento\PubSub\JobInterface
     */
    public function create(\Magento\PubSub\SubscriptionInterface $subscription, \Magento\PubSub\EventInterface $event)
    {
        return $this->_objectManager->create('Magento\Webhook\Model\Job', array(
            'data' => array(
                'event' => $event,
                'subscription' => $subscription
            )
        ));
    }
}
