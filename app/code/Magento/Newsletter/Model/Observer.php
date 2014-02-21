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
 * @category    Magento
 * @package     Magento_Newsletter
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Newsletter\Model;

use Magento\Cron\Model\Schedule;

/**
 * Newsletter module observer
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Observer
{
    /**
     * Queue collection factory
     *
     * @var \Magento\Newsletter\Model\Resource\Queue\CollectionFactory
     */
    protected $_queueCollectionFactory;

    /**
     * Subscriber factory
     *
     * @var \Magento\Newsletter\Model\SubscriberFactory
     */
    protected $_subscriberFactory;

    /**
     * Construct
     *
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     * @param \Magento\Newsletter\Model\Resource\Queue\CollectionFactory $queueCollectionFactory
     */
    public function __construct(
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        \Magento\Newsletter\Model\Resource\Queue\CollectionFactory $queueCollectionFactory
    ) {
        $this->_subscriberFactory = $subscriberFactory;
        $this->_queueCollectionFactory = $queueCollectionFactory;
    }

    /**
     * Subscribe customer handler
     *
     * @param \Magento\Object $observer
     * @return $this
     */
    public function subscribeCustomer($observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        if (($customer instanceof \Magento\Customer\Model\Customer)) {
            $this->_subscriberFactory->create()->subscribeCustomer($customer);
        }
        return $this;
    }

    /**
     * Customer delete handler
     *
     * @param \Magento\Object $observer
     * @return $this
     */
    public function customerDeleted($observer)
    {
        /** @var \Magento\Newsletter\Model\Subscriber $subscriber */
        $subscriber = $this->_subscriberFactory->create();
        $subscriber->loadByEmail($observer->getEvent()->getCustomer()->getEmail());
        if($subscriber->getId()) {
            $subscriber->delete();
        }
        return $this;
    }

    /**
     * Customer delete handler
     *
     * @param Schedule $schedule
     * @return void
     */
    public function scheduledSend($schedule)
    {
        $countOfQueue  = 3;
        $countOfSubscritions = 20;

        /** @var \Magento\Newsletter\Model\Resource\Queue\Collection $collection */
        $collection = $this->_queueCollectionFactory->create();
        $collection->setPageSize($countOfQueue)
            ->setCurPage(1)
            ->addOnlyForSendingFilter()
            ->load();

         $collection->walk('sendPerSubscriber', array($countOfSubscritions));
    }
}
