<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Observer;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * @magentoAppArea adminhtml
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpdateOrderStatusForPaymentMethodsObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Event\Observer
     */
    protected $_eventObserver;

    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $_objectManager;

    protected function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_eventObserver = $this->_createEventObserver();
    }

    /**
     * Check that \Magento\Payment\Observer\UpdateOrderStatusForPaymentMethodsObserver::execute()
     * is called as event and it can change status
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Payment/_files/order_status.php
     */
    public function testUpdateOrderStatusForPaymentMethodsEvent()
    {
        $statusCode = 'custom_new_status';
        $data = [
            'section' => 'payment',
            'website' => 1,
            'store' => 1,
            'groups' => ['checkmo' => ['fields' => ['order_status' => ['value' => $statusCode]]]],
        ];
        $this->_objectManager->create(
            \Magento\Config\Model\Config::class
        )->setSection(
            'payment'
        )->setWebsite(
            'base'
        )->setGroups(
            ['groups' => $data['groups']]
        )->save();

        /** @var \Magento\Sales\Model\Order\Status $status */
        $status = $this->_objectManager->get(\Magento\Sales\Model\Order\Status::class)->load($statusCode);

        /** @var $scopeConfig \Magento\Framework\App\Config\ScopeConfigInterface */
        $scopeConfig = $this->_objectManager->get(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $defaultStatus = (string)$scopeConfig->getValue(
            'payment/checkmo/order_status',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        /** @var \Magento\Config\Model\ResourceModel\Config $config */
        $config = $this->_objectManager->get(\Magento\Config\Model\ResourceModel\Config::class);
        $config->saveConfig(
            'payment/checkmo/order_status',
            $statusCode,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            0
        );

        $this->_resetConfig();

        $newStatus = (string)$scopeConfig->getValue(
            'payment/checkmo/order_status',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $status->unassignState(\Magento\Sales\Model\Order::STATE_NEW);

        $this->_resetConfig();

        $unassignedStatus = $scopeConfig->getValue(
            'payment/checkmo/order_status',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $this->assertEquals('pending', $defaultStatus);
        $this->assertEquals($statusCode, $newStatus);
        $this->assertEquals('pending', $unassignedStatus);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testUpdateOrderStatusForPaymentMethods()
    {
        $statusCode = 'custom_new_status';

        /** @var \Magento\Config\Model\ResourceModel\Config $config */
        $config = $this->_objectManager->get(\Magento\Config\Model\ResourceModel\Config::class);
        $config->saveConfig('payment/checkmo/order_status', $statusCode, 'default', 0);

        $this->_resetConfig();

        $observer = $this->_objectManager->create(
            \Magento\Payment\Observer\UpdateOrderStatusForPaymentMethodsObserver::class
        );
        $observer->execute($this->_eventObserver);

        $this->_resetConfig();

        /** @var $scopeConfig \Magento\Framework\App\Config\ScopeConfigInterface */
        $scopeConfig = $this->_objectManager->get(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $unassignedStatus = (string)$scopeConfig->getValue(
            'payment/checkmo/order_status',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $this->assertEquals('pending', $unassignedStatus);
    }

    /**
     * Create event observer
     *
     * @return \Magento\Framework\Event\Observer
     */
    protected function _createEventObserver()
    {
        $data = ['status' => 'custom_new_status', 'state' => \Magento\Sales\Model\Order::STATE_NEW];
        $event = $this->_objectManager->create(\Magento\Framework\Event::class, ['data' => $data]);
        return $this->_objectManager
            ->create(\Magento\Framework\Event\Observer::class, ['data' => ['event' => $event]]);
    }

    /**
     * Clear config cache
     */
    protected function _resetConfig()
    {
        $this->_objectManager->get(\Magento\Framework\App\Config\ReinitableConfigInterface::class)->reinit();
        $this->_objectManager->create(\Magento\Store\Model\StoreManagerInterface::class)->reinitStores();
    }
}
