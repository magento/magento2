<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\SignifydGateway\Request;

use Magento\Framework\Config\ScopeInterface;
use Magento\Signifyd\Model\SignifydOrderSessionId;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\App\Area;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Model\Order;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\ProductMetadataInterface;

/**
 * Class CreateCaseBuilderTest
 * @magentoAppIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreateCaseBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var CreateCaseBuilder
     */
    private $caseBuilder;

    /**
     * @var DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * Initial setup
     */
    protected function setUp()
    {
        $bootstrap = Bootstrap::getInstance();
        $bootstrap->loadArea(Area::AREA_FRONTEND);
        $this->objectManager = Bootstrap::getObjectManager();
        $this->dateTimeFactory = $this->objectManager->create(DateTimeFactory::class);
        $this->caseBuilder = $this->objectManager->create(CreateCaseBuilder::class);
    }

    /**
     * Test builder on order with customer, simple product, frontend area,
     * PayPal gateway, shipping and billing addresses, with two orders
     *
     * @covers \Magento\Signifyd\Model\SignifydGateway\Request\CreateCaseBuilder::build()
     * @magentoDataFixture Magento/Signifyd/_files/order_with_customer_and_two_simple_products.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCreateCaseBuilderWithFullSetOfData()
    {
        /** @var Order $order */
        $order = $this->objectManager->create(Order::class);
        $order->loadByIncrementId('100000001');
        
        $orderItems = $order->getAllItems();
        $product = $orderItems[0]->getProduct();
        $payment = $order->getPayment();
        $billingAddress = $order->getBillingAddress();
        $shippingAddress = $order->getShippingAddress();

        /** @var  CustomerRepositoryInterface $customerRepository */
        $customerRepository = $this->objectManager->create(CustomerRepositoryInterface::class);
        $customer = $customerRepository->getById($order->getCustomerId());

        $productMetadata = $this->objectManager->create(ProductMetadataInterface::class);

        /** @var SignifydOrderSessionId $signifydOrderSessionId */
        $signifydOrderSessionId = $this->objectManager->create(SignifydOrderSessionId::class);

        $expected = [
            'purchase' => [
                'orderSessionId' => $signifydOrderSessionId->get($order->getQuoteId()),
                'browserIpAddress' => $order->getRemoteIp(),
                'orderId' => $order->getIncrementId(),
                'createdAt' => '2016-12-12T12:00:55+00:00',
                'paymentGateway' => 'paypal_account',
                'transactionId' => $payment->getLastTransId(),
                'currency' => $order->getOrderCurrencyCode(),
                'orderChannel' => 'WEB',
                'totalPrice' => $order->getGrandTotal(),
                'shipments' => [
                    0 => [
                        'shipper' => 'Flat Rate',
                        'shippingMethod' => 'Fixed',
                        'shippingPrice' => $order->getShippingAmount()
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
                    ],
                    1 => [
                        'itemId' => $orderItems[1]->getSku(),
                        'itemName' => $orderItems[1]->getName(),
                        'itemPrice' => $orderItems[1]->getPrice(),
                        'itemQuantity' => $orderItems[1]->getQtyOrdered(),
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
                    'streetAddress' => '6161 West Centinela Avenue',
                    'unit' => 'app. 33',
                    'city' => $shippingAddress->getCity(),
                    'provinceCode' => $shippingAddress->getRegionCode(),
                    'postalCode' => $shippingAddress->getPostcode(),
                    'countryCode' => $shippingAddress->getCountryId()
                ]
            ],
            'userAccount' => [
                'email' => $customer->getEmail(),
                'username' => $customer->getEmail(),
                'phone' => $order->getBillingAddress()->getTelephone(),
                'accountNumber' => $customer->getId(),
                'createdDate' => $this->formatDate($customer->getCreatedAt()),
                'lastUpdateDate' => $this->formatDate($customer->getUpdatedAt()),
                'aggregateOrderCount' => 2,
                'aggregateOrderDollars' => 150.0
            ],
            'seller' => $this->getSellerData(),
            'clientVersion' => [
                'platform' => $productMetadata->getName() . ' ' . $productMetadata->getEdition(),
                'platformVersion' => $productMetadata->getVersion()
            ]
        ];

        static::assertEquals(
            $expected,
            $this->caseBuilder->build($order->getEntityId())
        );
    }

    /**
     * Test builder on order with guest, virtual product, admin area,
     * none PayPal gateway, no shipping address, without credit card data
     *
     * @covers \Magento\Signifyd\Model\SignifydGateway\Request\CreateCaseBuilder::build()
     * @magentoDataFixture Magento/Signifyd/_files/order_with_guest_and_virtual_product.php
     */
    public function testCreateCaseBuilderWithVirtualProductAndGuest()
    {
        /** @var Order $order */
        $order = $this->objectManager->create(Order::class);
        $order->loadByIncrementId('100000002');

        $scope = $this->objectManager->get(ScopeInterface::class);
        $scope->setCurrentScope(Area::AREA_ADMINHTML);

        $orderItems = $order->getAllItems();
        $product = $orderItems[0]->getProduct();
        $payment = $order->getPayment();
        $billingAddress = $order->getBillingAddress();
        $productMetadata = $this->objectManager->create(ProductMetadataInterface::class);

        /** @var SignifydOrderSessionId $quoteSessionId */
        $quoteSessionId = $this->objectManager->create(SignifydOrderSessionId::class);

        $expected = [
            'purchase' => [
                'orderSessionId' => $quoteSessionId->get($order->getQuoteId()),
                'browserIpAddress' => $order->getRemoteIp(),
                'orderId' => $order->getIncrementId(),
                'createdAt' => '2016-12-12T12:00:55+00:00',
                'paymentGateway' => $payment->getMethod(),
                'transactionId' => $payment->getLastTransId(),
                'currency' => $order->getOrderCurrencyCode(),
                'orderChannel' => 'PHONE',
                'totalPrice' => $order->getGrandTotal(),
                'products' => [
                    0 => [
                        'itemId' => $orderItems[0]->getSku(),
                        'itemName' => $orderItems[0]->getName(),
                        'itemPrice' => $orderItems[0]->getPrice(),
                        'itemQuantity' => $orderItems[0]->getQtyOrdered(),
                        'itemUrl' => $product->getProductUrl()
                    ],
                ]
            ],
            'card' => [
                'cardHolderName' => 'firstname lastname',
                'billingAddress' => [
                    'streetAddress' => 'street',
                    'city' => $billingAddress->getCity(),
                    'provinceCode' => $billingAddress->getRegionCode(),
                    'postalCode' => $billingAddress->getPostcode(),
                    'countryCode' => $billingAddress->getCountryId()
                ]
            ],
            'seller' => $this->getSellerData(),
            'clientVersion' => [
                'platform' => $productMetadata->getName() . ' ' . $productMetadata->getEdition(),
                'platformVersion' => $productMetadata->getVersion()
            ]
        ];

        static::assertEquals(
            $expected,
            $this->caseBuilder->build($order->getEntityId())
        );
    }

    /**
     * Return seller data according to fixture
     *
     * @return array
     */
    private function getSellerData()
    {
        return [
            'name' => 'Sample Store',
            'domain' => 'm2.com',
            'shipFromAddress' => [
                'streetAddress' => '6161 West Centinela Avenue',
                'unit' => 'app. 111',
                'city' => 'Culver City',
                'provinceCode' => 'AE',
                'postalCode' => '90230',
                'countryCode' => 'US',
            ],
            'corporateAddress' => [
                'streetAddress' => '5th Avenue',
                'unit' => '75',
                'city' => 'New York',
                'provinceCode' => 'MH',
                'postalCode' => '19032',
                'countryCode' => 'US',
            ],
        ];
    }

    /**
     * Format date in ISO8601
     *
     * @param string $date
     * @return string
     */
    private function formatDate($date)
    {
        $result = $this->dateTimeFactory->create(
            $date,
            new \DateTimeZone('UTC')
        );

        return $result->format(\DateTime::ATOM);
    }
}
