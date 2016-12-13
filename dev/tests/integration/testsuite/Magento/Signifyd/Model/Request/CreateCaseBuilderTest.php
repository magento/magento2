<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\Request;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\App\Area;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Model\Order;

/**
 * Class PurchaseBuilderTest
 * @magentoAppIsolation enabled
 * @package Magento\Signifyd\Model\Request\CreateCaseBuilder
 */
class CreateCaseBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Order increment ID
     */
    const ORDER_INCREMENT_ID = '100000001';

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Order
     */
    private $order;

    /**
     * @var CreateCaseBuilder
     */
    private $caseBuilder;

    /**
     * @var array
     */
    private $builderData;

    /**
     * Initial setup
     */
    protected function setUp()
    {
        $bootstrap = Bootstrap::getInstance();
        $bootstrap->loadArea(Area::AREA_FRONTEND);
        $this->objectManager = Bootstrap::getObjectManager();

        $this->order = $this->objectManager->create(Order::class);
        $this->order->loadByIncrementId(self::ORDER_INCREMENT_ID);

        $this->caseBuilder = $this->objectManager->create(CreateCaseBuilder::class);
        $this->builderData = $this->caseBuilder->build($this->order->getEntityId());
    }

    /**
     * Check the stability purchaseBuilder
     *
     * @magentoDataFixture Magento/Signifyd/_files/order.php
     */
    public function testPurchaseBuilder()
    {
        $orderMethod = 'paypal_account';
        $orderChannel = 'WEB';
        $shippingProvider = 'Flat Rate';
        $shippingMethod = 'Fixed';

        $purchaseData = $this->builderData['purchase'];

        $dateTimeFactory = $this->objectManager->get(DateTimeFactory::class);
        $createdAt = $dateTimeFactory->create(
            $this->order->getCreatedAt(),
            new \DateTimeZone('UTC')
        );

        $orderPayment = $this->order->getPayment();

        $orderItems = $this->order->getAllItems();
        $product = $orderItems[0]->getProduct();
        $purchaseProducts = $purchaseData['products'][0];

        static::assertEquals($this->order->getRemoteIp(), $purchaseData['browserIpAddress']);
        static::assertEquals($shippingProvider, $purchaseData['shipments'][0]['shipper']);
        static::assertEquals($shippingMethod, $purchaseData['shipments'][0]['shippingMethod']);
        //static::assertEquals($this->order->getShippingAmount(), $purchaseData['shipments'][0]['shippingPrice']);
        static::assertEquals($this->order->getEntityId(), $purchaseData['orderId']);
        static::assertEquals($createdAt->format(\DateTime::ISO8601), $purchaseData['createdAt']);

        static::assertEquals($orderMethod, $purchaseData['paymentGateway']);
        static::assertEquals($orderPayment->getLastTransId(), $purchaseData['transactionId']);
        static::assertEquals($this->order->getOrderCurrencyCode(), $purchaseData['currency']);
        static::assertEquals($orderChannel, $purchaseData['orderChannel']);
        static::assertEquals($this->order->getGrandTotal(), $purchaseData['totalPrice']);

        //static::assertEquals($product->getSku(), $purchaseProducts['itemId']);
        //static::assertEquals($product->getName(), $purchaseProducts['itemName']);
        static::assertEquals($orderItems[0]->getPrice(), $purchaseProducts['itemPrice']);
        static::assertEquals($orderItems[0]->getQtyOrdered(), $purchaseProducts['itemQuantity']);
        static::assertEquals($product->getProductUrl(), $purchaseProducts['itemUrl']);
        static::assertEquals($product->getWeight(), $purchaseProducts['itemWeight']);
    }
}
