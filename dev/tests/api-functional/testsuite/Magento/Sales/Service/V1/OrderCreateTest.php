<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Sales\Service\V1;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Webapi\Model\Rest\Config;

class OrderCreateTest extends WebapiAbstract
{
    const RESOURCE_PATH = '/V1/order';

    const SERVICE_READ_NAME = 'salesOrderRepositoryV1';

    const SERVICE_VERSION = 'V1';

    const ORDER_INCREMENT_ID = '100000001';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    protected function prepareOrder()
    {
        /** @var \Magento\Sales\Model\Order $orderBuilder */
        $orderFactory = $this->objectManager->get('Magento\Sales\Model\OrderFactory');
        /** @var \Magento\Sales\Service\V1\Data\OrderItemBuilder $orderItemBuilder */
        $orderItemFactory = $this->objectManager->get('Magento\Sales\Model\Order\ItemFactory');
        /** @var \Magento\Sales\Service\V1\Data\OrderPaymentBuilder $orderPaymentBuilder */
        $orderPaymentFactory = $this->objectManager->get('Magento\Sales\Model\Order\PaymentFactory');
        /** @var \Magento\Sales\Service\V1\Data\OrderAddressBuilder $orderAddressBuilder */
        $orderAddressFactory = $this->objectManager->get('Magento\Sales\Model\Order\AddressFactory');

        $order = $orderFactory->create(
            ['data' => $this->getDataStructure('Magento\Sales\Api\Data\OrderInterface')]
        );
        $orderItem = $orderItemFactory->create(
            ['data' => $this->getDataStructure('Magento\Sales\Api\Data\OrderItemInterface')]
        );
        $orderPayment = $orderPaymentFactory->create(
            ['data' => $this->getDataStructure('Magento\Sales\Api\Data\OrderPaymentInterface')]
        );
        $orderAddressBilling = $orderAddressFactory->create(
            ['data' => $this->getDataStructure('Magento\Sales\Api\Data\OrderAddressInterface')]
        );

        $email = uniqid() . 'email@example.com';
        $orderItem->setSku('sku#1');
        $orderPayment->setCcLast4('4444');
        $orderPayment->setMethod('checkmo');
        $orderPayment->setAccountStatus('ok');
        $orderPayment->setAdditionalInformation([]);
        $order->setCustomerEmail($email);
        $order->setBaseGrandTotal(100);
        $order->setGrandTotal(100);
        $order->setItems([$orderItem->getData()]);
        $order->setPayments([$orderPayment->getData()]);
        $orderAddressBilling->setCity('City');
        $orderAddressBilling->setPostcode('12345');
        $orderAddressBilling->setLastname('Last Name');
        $orderAddressBilling->setFirstname('First Name');
        $orderAddressBilling->setTelephone('+00(000)-123-45-57');
        $orderAddressBilling->setStreet(['Street']);
        $orderAddressBilling->setCountryId(1);
        $orderAddressBilling->setAddressType('billing');

        $orderAddressShipping = $orderAddressFactory->create(
            ['data' => $this->getDataStructure('Magento\Sales\Api\Data\OrderAddressInterface')]
        );
        $orderAddressShipping->setCity('City');
        $orderAddressShipping->setPostcode('12345');
        $orderAddressShipping->setLastname('Last Name');
        $orderAddressShipping->setFirstname('First Name');
        $orderAddressShipping->setTelephone('+00(000)-123-45-57');
        $orderAddressShipping->setStreet(['Street']);
        $orderAddressShipping->setCountryId(1);
        $orderAddressShipping->setAddressType('shipping');

        $orderData = $order->getData();
        $orderData['billing_address'] = $orderAddressBilling->getData();
        $orderData['billing_address']['street'] = ['Street'];
        $orderData['shipping_address'] = $orderAddressShipping->getData();
        $orderData['shipping_address']['street'] = ['Street'];
        return $orderData;
    }

    protected function getDataStructure($className)
    {
        $refClass = new \ReflectionClass($className);
        $constants = $refClass->getConstants();
        $data = array_fill_keys($constants, null);
        unset($data['custom_attributes']);
        return $data;
    }

    public function testOrderCreate()
    {
        $order = $this->prepareOrder();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => Config::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_READ_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_READ_NAME . 'save',
            ],
        ];
        $this->assertNotEmpty($this->_webApiCall($serviceInfo, ['entity' => $order]));

        /** @var \Magento\Sales\Model\Order $model */
        $model = $this->objectManager->get('Magento\Sales\Model\Order');
        $model->load($order['customer_email'], 'customer_email');
        $this->assertTrue((bool)$model->getId());
        $this->assertEquals($order['base_grand_total'], $model->getBaseGrandTotal());
        $this->assertEquals($order['grand_total'], $model->getGrandTotal());
    }
}
