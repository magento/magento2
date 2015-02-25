<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order;

use Magento\TestFramework\Helper\Bootstrap;

class AddressTest extends \PHPUnit_Framework_TestCase
{
    /** @var Address */
    protected $_model;

    protected function setUp()
    {
        $this->_model = Bootstrap::getObjectManager()->create('Magento\Sales\Model\Order\Address');
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     */
    public function testSave()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = Bootstrap::getObjectManager()->create('Magento\Sales\Model\Order');
        $order->loadByIncrementId('100000001');
        $this->_model->setOrder($order);
        $this->_model->setEmail('co@co.co');
        $this->_model->setPostcode('12345');
        $this->_model->setLastname('LastName');
        $this->_model->setStreet('Street');
        $this->_model->setCity('City');
        $this->_model->setTelephone('123-45-67');
        $this->_model->setCountryId(1);
        $this->_model->setFirstname('FirstName');
        $this->_model->setAddressType('billing');
        $this->_model->setRegionId(1);
        $this->_model->save();
        $this->assertEquals($order->getId(), $this->_model->getParentId());
    }
}
