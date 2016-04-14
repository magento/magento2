<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\Hostedpro;

use Magento\Paypal\Model\Hostedpro\Request;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Sales\Model\Order;

/**
 * Class RequestTest
 * @package Magento\Paypal\Model
 */
class RequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Paypal\Model\Hostedpro\Request
     */
    private $model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();

        $this->model = $this->objectManager->create(Request::class);
    }

    /**
     * @covers \Magento\Paypal\Model\Hostedpro\Request::setOrder()
     * @magentoDataFixture Magento/Paypal/_files/order_hostedpro.php
     */
    public function testSetOrder()
    {
        $incrementId = '100000001';
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->objectManager->create(Order::class);
        $order->loadByIncrementId($incrementId);

        $this->model->setOrder($order);
        $addressData = require(__DIR__ . '/../../_files/address_data.php');
        static::assertEquals($incrementId, $this->model->getInvoice());

        $this->assertAddress($addressData, 'billing');
        $this->assertAddress($addressData);
    }

    /**
     * Assert address details
     *
     * @param array $address
     * @param string $type
     */
    protected function assertAddress(array $address, $type = '')
    {
        $type = !empty($type) ? $type . '_' : '';

        static::assertEquals($address['firstname'], $this->model->getData($type.'first_name'));
        static::assertEquals($address['lastname'], $this->model->getData($type.'last_name'));
        static::assertEquals($address['city'], $this->model->getData($type.'city'));
        static::assertEquals($address['region'], $this->model->getData($type.'state'));
        static::assertEquals($address['country_id'], $this->model->getData($type.'country'));
        static::assertEquals($address['postcode'], $this->model->getData($type.'zip'));
        static::assertEquals($address['street'], $this->model->getData($type.'address1'));
    }
}
