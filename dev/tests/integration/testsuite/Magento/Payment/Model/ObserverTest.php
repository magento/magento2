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
 * @package     Magento_Payment
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Payment\Model;

/**
 * @magentoAppArea adminhtml
 */
class ObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Event\Observer
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
     * Check that \Magento\Payment\Model\Observer::updateOrderStatusForPaymentMethods()
     * is called as event and it can change status
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Payment/_files/order_status.php
     */
    public function testUpdateOrderStatusForPaymentMethodsEvent()
    {
        $statusCode = 'custom_new_status';
        $data = array(
            'section' => 'payment',
            'website' => 1,
            'store' => 1,
            'groups' => array('checkmo' => array('fields' => array('order_status' => array('value' => $statusCode))))
        );
        $this->_objectManager->create(
            'Magento\Backend\Model\Config'
        )->setSection(
            'payment'
        )->setWebsite(
            'base'
        )->setGroups(
            array('groups' => $data['groups'])
        )->save();

        /** @var \Magento\Sales\Model\Order\Status $status */
        $status = $this->_objectManager->get('Magento\Sales\Model\Order\Status')->load($statusCode);

        /** @var $storeConfig \Magento\Core\Model\Store\Config */
        $storeConfig = $this->_objectManager->get('Magento\Core\Model\Store\Config');
        $defaultStatus = (string)$storeConfig->getConfig('payment/checkmo/order_status');

        /** @var \Magento\Core\Model\Resource\Config $config */
        $config = $this->_objectManager->get('Magento\Core\Model\Resource\Config');
        $config->saveConfig('payment/checkmo/order_status', $statusCode, 'default', 0);

        $this->_resetConfig();

        $newStatus = $storeConfig->getConfig('payment/checkmo/order_status');

        $status->unassignState(\Magento\Sales\Model\Order::STATE_NEW);

        $this->_resetConfig();

        $unassignedStatus = $storeConfig->getConfig('payment/checkmo/order_status');

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

        /** @var \Magento\Core\Model\Resource\Config $config */
        $config = $this->_objectManager->get('Magento\Core\Model\Resource\Config');
        $config->saveConfig('payment/checkmo/order_status', $statusCode, 'default', 0);

        $this->_resetConfig();

        $observer = $this->_objectManager->create('Magento\Payment\Model\Observer');
        $observer->updateOrderStatusForPaymentMethods($this->_eventObserver);

        $this->_resetConfig();

        /** @var \Magento\Core\Model\Store\Config $storeConfig */
        $storeConfig = $this->_objectManager->get('Magento\Core\Model\Store\Config');
        $unassignedStatus = $storeConfig->getConfig('payment/checkmo/order_status');
        $this->assertEquals('pending', $unassignedStatus);
    }

    /**
     * Create event observer
     *
     * @return \Magento\Event\Observer
     */
    protected function _createEventObserver()
    {
        $data = array('status' => 'custom_new_status', 'state' => \Magento\Sales\Model\Order::STATE_NEW);
        $event = $this->_objectManager->create('Magento\Event', array('data' => $data));
        return $this->_objectManager->create('Magento\Event\Observer', array('data' => array('event' => $event)));
    }

    /**
     * Clear config cache
     */
    protected function _resetConfig()
    {
        $this->_objectManager->get('Magento\App\ReinitableConfigInterface')->reinit();
        $this->_objectManager->create('Magento\Core\Model\StoreManagerInterface')->reinitStores();
    }
}
