<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Service\V1;

use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Catalog\Api\Data\ProductCustomOptionInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OrderCreateTest extends WebapiAbstract
{
    private const RESOURCE_PATH = '/V1/orders';

    private const SERVICE_READ_NAME = 'salesOrderRepositoryV1';

    private const SERVICE_VERSION = 'V1';

    private const ORDER_INCREMENT_ID = '100000001';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function prepareOrder()
    {
        /** @var \Magento\Sales\Model\Order $orderBuilder */
        $orderFactory = $this->objectManager->get(\Magento\Sales\Model\OrderFactory::class);
        /** @var \Magento\Sales\Api\Data\OrderItemFactory $orderItemFactory */
        $orderItemFactory = $this->objectManager->get(\Magento\Sales\Model\Order\ItemFactory::class);
        /** @var \Magento\Sales\Api\Data\OrderPaymentFactory $orderPaymentFactory */
        $orderPaymentFactory = $this->objectManager->get(\Magento\Sales\Model\Order\PaymentFactory::class);
        /** @var \Magento\Sales\Model\Order\AddressRepository $orderAddressRepository */
        $orderAddressRepository = $this->objectManager->get(\Magento\Sales\Model\Order\AddressRepository::class);
        /** @var  \Magento\Store\Model\StoreManagerInterface $storeManager */
        $storeManager = $this->objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);

        $order = $orderFactory->create(
            ['data' => $this->getDataStructure(\Magento\Sales\Api\Data\OrderInterface::class)]
        );
        $orderItem = $orderItemFactory->create(
            ['data' => $this->getDataStructure(\Magento\Sales\Api\Data\OrderItemInterface::class)]
        );
        $orderPayment = $orderPaymentFactory->create(
            ['data' => $this->getDataStructure(\Magento\Sales\Api\Data\OrderPaymentInterface::class)]
        );

        $email = uniqid() . 'email@example.com';
        $orderItem->setSku('sku#1');
        if (TESTS_WEB_API_ADAPTER == self::ADAPTER_SOAP) {
            $orderItem->setData('parent_item', $orderItem->getData() + ['parent_item' => null]);
            $orderItem->setAdditionalData('test');
        } else {
            $orderItem->setData('parent_item', ['weight' => 1]);
        }

        $orderPayment->setCcLast4('4444');
        $orderPayment->setMethod('checkmo');
        $orderPayment->setAccountStatus('ok');
        $orderPayment->setAdditionalInformation([]);
        $order->setCustomerEmail($email);
        $order->setBaseGrandTotal(100);
        $order->setGrandTotal(100);
        $order->setShippingDescription('Flat Rate - Fixed');
        $order->setIsVirtual(0);
        $order->setStoreId($storeManager->getDefaultStoreView()->getId());
        $order->setBaseDiscountAmount(0);
        $order->setBaseShippingAmount(5);
        $order->setBaseShippingTaxAmount(0);
        $order->setBaseSubtotal(100);
        $order->setBaseTaxAmount(0);
        $order->setBaseToGlobalRate(1);
        $order->setBaseToOrderRate(1);
        $order->setDiscountAmount(0);
        $order->setShippingAmount(0);
        $order->setShippingTaxAmount(0);
        $order->setStoreToOrderRate(0);
        $order->setBaseToOrderRate(0);
        $order->setSubtotal(100);
        $order->setTaxAmount(0);
        $order->setTotalQtyOrdered(1);
        $order->setCustomerIsGuest(1);
        $order->setCustomerNoteNotify(0);
        $order->setCustomerGroupId(0);
        $order->setBaseSubtotalInclTax(100);
        $order->setWeight(1);
        $order->setBaseCurrencyCode('USD');
        $order->setShippingInclTax(5);
        $order->setBaseShippingInclTax(5);

        $this->addProductOption($orderItem);

        $order->setItems([$orderItem->getData()]);
        $order->setData('payment', $orderPayment->getData());

        $orderAddressBilling = $orderAddressRepository->create();

        $orderAddressBilling->setCity('City');
        $orderAddressBilling->setPostcode('12345');
        $orderAddressBilling->setLastname('Last Name');
        $orderAddressBilling->setFirstname('First Name');
        $orderAddressBilling->setTelephone('+00(000)-123-45-57');
        $orderAddressBilling->setStreet(['Street']);
        $orderAddressBilling->setCountryId('US');
        $orderAddressBilling->setRegion('California');
        $orderAddressBilling->setAddressType('billing');
        $orderAddressBilling->setRegionId(12);

        $orderAddressShipping = $orderAddressRepository->create();
        $orderAddressShipping->setCity('City2');
        $orderAddressShipping->setPostcode('12345');
        $orderAddressShipping->setLastname('Last Name2');
        $orderAddressShipping->setFirstname('First Name2');
        $orderAddressShipping->setTelephone('+00(000)-123-45-57');
        $orderAddressShipping->setStreet(['Street']);
        $orderAddressShipping->setCountryId('US');
        $orderAddressShipping->setRegion('California');
        $orderAddressShipping->setAddressType('shipping');
        $orderAddressShipping->setRegionId(12);

        $orderData = $order->getData();
        $orderData['billing_address'] = $orderAddressBilling->getData();
        $orderData['billing_address']['street'] = ['Street'];
        $address = $orderAddressShipping->getData();
        $address['street'] = ['Street'];
        $orderData['extension_attributes']['shipping_assignments'] =
            [
                [
                    'shipping' => [
                        'address' => $address,
                        'method' => 'flatrate_flatrate'
                    ],
                    'items' => [$orderItem->getData()],
                    'stock_id' => null,
                ]
            ];
        $orderData['extension_attributes']['taxes'] = [
            [
                'code' => 'US-NY-*-Rate 1',
                'title' => 'US-NY-*-Rate 1',
                'percent' => 5,
                'amount' => 0.75,
                'base_amount' => 0.75,
                'base_real_amount' => 0.75,
                'position' => 0,
                'priority' => 0,
                'process' => 0
            ],
        ];
        $orderData['extension_attributes']['additional_itemized_taxes'] = [
            [
                'tax_percent' => 5,
                'tax_code' => 'US-NY-*-Rate 1',
                'amount' => 0.25,
                'base_amount' => 0.25,
                'real_amount' => 0.25,
                'real_base_amount' => 0.25,
                'taxable_item_type' => 'shipping',
            ]
        ];
        $orderData['items'][0]['extension_attributes']['itemized_taxes'] = [
            [
                'tax_percent' => 5,
                'tax_code' => 'US-NY-*-Rate 1',
                'amount' => 0.5,
                'base_amount' => 0.5,
                'real_amount' => 0.5,
                'real_base_amount' => 0.5,
                'taxable_item_type' => 'product',
            ]
        ];
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

    /**
     * @param array $orderItem
     * @return void
     */
    protected function addProductOption($orderItem)
    {
        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $product = $productRepository->get('simple');
        $options = [];
        foreach ($product->getOptions() as $option) {
            $options[] = [
                'option_id' => $option->getId(),
                'option_value' => $this->getOptionRequestValue($option),
            ];
        }
        $data['extension_attributes']['custom_options'] = $options;
        $orderItem->setData('product_option', $data);
        $orderItem->setPrice(10);
        $orderItem->setBasePrice(10);
    }

    /**
     * @param ProductCustomOptionInterface $option
     * @return null|string
     */
    protected function getOptionRequestValue(ProductCustomOptionInterface $option)
    {
        $returnValue = null;
        switch ($option->getType()) {
            case 'field':
                $returnValue = 'Test value';
                break;
            case 'date_time':
                $returnValue = '2015-09-09 07:16:00';
                break;
            case 'drop_down':
                $returnValue = '3-1-select';
                break;
            case 'radio':
                $returnValue = '4-1-radio';
                break;
        }
        return $returnValue;
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testOrderCreate()
    {
        $order = $this->prepareOrder();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_READ_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_READ_NAME . 'save',
            ],
        ];
        $result = $this->_webApiCall($serviceInfo, ['entity' => $order]);

        $getServiceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $result['entity_id'],
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_READ_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_READ_NAME . 'get',
            ],
        ];
        $result = $this->_webApiCall($getServiceInfo, ['id' => $result['entity_id']]);

        $this->assertEquals(100, $result['base_grand_total']);
        $this->assertEquals(100, $result['grand_total']);
        $shipping = $result['extension_attributes']['shipping_assignments'][0]['shipping'];
        $this->assertGreaterThan(0, $shipping['address']['entity_id']);
        $this->assertEquals(['Street'], $shipping['address']['street']);
        $this->assertEquals('flatrate_flatrate', $shipping['method']);
        $taxes = $result['extension_attributes']['taxes'];
        $this->assertCount(1, $taxes);
        $this->assertEquals('US-NY-*-Rate 1', $taxes[0]['code']);
        $this->assertEquals('US-NY-*-Rate 1', $taxes[0]['title']);
        $this->assertEquals(5, $taxes[0]['percent']);
        $this->assertEquals(0.75, $taxes[0]['amount']);
        $this->assertEquals(0.75, $taxes[0]['base_amount']);
        $this->assertCount(1, $result['extension_attributes']['additional_itemized_taxes']);
        $shippingTaxItem = $result['extension_attributes']['additional_itemized_taxes'][0];
        $this->assertEquals('shipping', $shippingTaxItem['taxable_item_type']);
        $this->assertEquals(5, $shippingTaxItem['tax_percent']);
        $this->assertEquals(0.25, $shippingTaxItem['amount']);
        $this->assertEquals(0.25, $shippingTaxItem['base_amount']);
        $this->assertEquals(0.25, $shippingTaxItem['real_amount']);
        $this->assertCount(1, $result['items'][0]['extension_attributes']['itemized_taxes']);
        $orderItemTaxItem = $result['items'][0]['extension_attributes']['itemized_taxes'][0];
        $this->assertEquals('product', $orderItemTaxItem['taxable_item_type']);
        $this->assertEquals(5, $orderItemTaxItem['tax_percent']);
        $this->assertEquals(0.50, $orderItemTaxItem['amount']);
        $this->assertEquals(0.50, $orderItemTaxItem['base_amount']);
        $this->assertEquals(0.50, $orderItemTaxItem['real_amount']);
        $this->assertEquals($result['items'][0]['item_id'], $orderItemTaxItem['item_id']);
    }
}
