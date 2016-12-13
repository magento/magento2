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
use Magento\Customer\Api\CustomerRepositoryInterface;

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
        $orderItems = $this->order->getAllItems();
        $product = $orderItems[0]->getProduct();
        $payment = $this->order->getPayment();
        $billingAddress = $this->order->getBillingAddress();
        $shippingAddress = $this->order->getShippingAddress();

        $customerRepository = $this->objectManager->create(CustomerRepositoryInterface::class);
        $customer = $customerRepository->getById(1);

        $expected = [
            'purchase' => [
                'browserIpAddress' => $this->order->getRemoteIp(),
                'orderId' => $this->order->getEntityId(),
                'createdAt' => '2016-12-12T12:00:55+00:00',
                'paymentGateway' => 'paypal_account',
                'transactionId' => $payment->getLastTransId(),
                'currency' => $this->order->getOrderCurrencyCode(),
                'orderChannel' => 'WEB',
                'totalPrice' => $this->order->getGrandTotal(),
                'shipments' => [
                    0 => [
                        'shipper' => 'Flat Rate',
                        'shippingMethod' => 'Fixed',
                        'shippingPrice' => $this->order->getShippingAmount()
                    ]
                ],
                'products' => [
                    0 => [
                        'itemId' => $orderItems[0]->getSku(),
                        'itemName' => $orderItems[0]->getName(),
                        'itemPrice' => $orderItems[0]->getPrice(),
                        'itemQuantity' => $orderItems[0]->getQtyOrdered(),
                        'itemUrl' => $product->getProductUrl(),
                        'itemWeight' => $product->getWeight()
                    ]
                ]
            ],
            'card' => [
                'cardHolderName' => 'firstname lastname',
                'last4' => $payment->getCcLast4(),
                'expiryMonth' => $payment->getCcExpMonth(),
                'expiryYear' =>  $payment->getCcExpYear(),
                'billingAddress' => [
                    'streetAddress' => 'street',
                    'city' => $billingAddress->getCity(),
                    'provinceCode' => $billingAddress->getRegionCode(),
                    'postalCode' => $billingAddress->getPostcode(),
                    'countryCode' => $billingAddress->getCountryId()
                ]
            ],
            'recipient' => [
                'fullName' => $shippingAddress->getName(),
                'confirmationEmail' =>  $shippingAddress->getEmail(),
                'confirmationPhone' => $shippingAddress->getTelephone(),
                'deliveryAddress' => [
                    'streetAddress' => 'street',
                    'city' => $shippingAddress->getCity(),
                    'provinceCode' => $shippingAddress->getRegionCode(),
                    'postalCode' => $shippingAddress->getPostcode(),
                    'countryCode' => $shippingAddress->getCountryId()
                ]
            ],
            'userAccount' => [
                'email' => $customer->getEmail(),
                'username' => $customer->getEmail(),
                'phone' => $this->order->getBillingAddress()->getTelephone(),
                'accountNumber' => $customer->getId(),
                'createdDate' => '2016-12-12 11:00:00+00:00',
                'lastUpdateDate' => '2016-12-12 11:05:00+00:00'
            ]
        ];

        static::assertEquals($expected['userAccount'], $this->builderData['userAccount']);
    }
}
