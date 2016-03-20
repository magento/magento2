<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Handler\OrderInjectable;

use Magento\Bundle\Test\Fixture\BundleProduct;
use Magento\ConfigurableProduct\Test\Fixture\ConfigurableProduct;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Downloadable\Test\Fixture\DownloadableProduct;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\SalesRule\Test\Fixture\SalesRule;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Handler\Curl as AbstractCurl;
use Magento\Mtf\Util\Protocol\CurlTransport;
use Magento\Mtf\Util\Protocol\CurlTransport\BackendDecorator;

/**
 * Create new order via curl.
 */
class Curl extends AbstractCurl implements OrderInjectableInterface
{
    /**
     * Customer fixture.
     *
     * @var Customer
     */
    protected $customer;

    /**
     * Customer fixture.
     *
     * @var OrderInjectable
     */
    protected $order;

    /**
     * Mapping values for data.
     *
     * @var array
     */
    protected $mappingData = [
        'region_id' => [
            'California' => '12',
        ],
        'country_id' => [
            'United States' => 'US',
        ],
    ];

    /**
     * Steps for create order on backend.
     *
     * @var array
     */
    protected $steps = [
        'customer_choice' => 'header,data',
        'products_choice' => 'search,items,shipping_method,totals,giftmessage,billing_method',
        'apply_coupon_code' => 'items,shipping_method,totals,billing_method',
        'shipping_data_address' => 'shipping_method,billing_method,shipping_address,totals,giftmessage',
        'shipping_data_method_get' => 'shipping_method,totals',
        'shipping_data_method_set' => 'shipping_method,totals,billing_method',
    ];

    /**
     * Post request for creating order.
     *
     * @param FixtureInterface|null $fixture [optional]
     * @return array
     */
    public function persist(FixtureInterface $fixture = null)
    {
        $this->order = $fixture;
        $this->customer = $fixture->getDataFieldConfig('customer_id')['source']->getCustomer();
        $data = $this->replaceMappingData($this->prepareData($fixture));
        return ['id' => $this->createOrder($data)];
    }

    /**
     * Prepare POST data for creating product request.
     *
     * @param FixtureInterface $fixture
     * @return array
     */
    protected function prepareData(FixtureInterface $fixture)
    {
        $result = [];
        $data = $fixture->getData();
        $result['customer_choice'] = $this->prepareCustomerData($data);
        $result['products_choice'] = $this->prepareProductsData($data['entity_id']);
        if (isset($data['coupon_code'])) {
            $result['apply_coupon_code'] = $this->prepareCouponCode($data['coupon_code']);
        }
        $result['order_data'] = $this->prepareOrderData($data);
        $result['shipping_data_address'] = $this->prepareShippingData($result['order_data']);
        $result['shipping_data_method_get'] = [
            'payment' => $data['payment_auth_expiration'],
            'collect_shipping_rates' => 1,
        ];
        $result['shipping_data_method_set'] = [
            'order' => ['shipping_method' => $result['order_data']['order']['shipping_method']],
            'payment' => $data['payment_auth_expiration'],
        ];

        return $result;
    }

    /**
     * Prepare coupon data.
     *
     * @param SalesRule $data
     * @return array
     */
    protected function prepareCouponCode(SalesRule $data)
    {
        return ['order' => ['coupon' => ['code' => $data->getCouponCode()]]];
    }

    /**
     * Prepare shipping data.
     *
     * @param array $data
     * @return array
     */
    protected function prepareShippingData(array $data)
    {
        $result = [
            'order' => [
                'billing_address' => $data['billing_address'],
            ],
            'payment' => $this->order->getPaymentAuthExpiration(),
            'reset_shipping' => 1,
            'shipping_as_billing' => 1,
        ];
        return $result;
    }

    /**
     * Prepare products data.
     *
     * @param array $data
     * @return array
     */
    protected function prepareProductsData(array $data)
    {
        $result['item'] = [];
        foreach ($data['products'] as $value) {
            if (!$value->hasData('checkout_data')) {
                continue;
            }
            $methodName = 'prepare' . ucfirst($value->getDataConfig()['type_id']) . 'Data';
            if (!method_exists($this, $methodName)) {
                $methodName = 'prepareSimpleData';
            }
            $result['item'][$value->getId()] = $this->$methodName($value);
        }
        return $result;
    }

    /**
     * Prepare data for configurable product.
     *
     * @param ConfigurableProduct $product
     * @return array
     */
    protected function prepareConfigurableData(ConfigurableProduct $product)
    {
        $result = [];
        $checkoutData = $product->getCheckoutData();
        $result['qty'] = $checkoutData['qty'];
        $attributesData = $product->hasData('configurable_attributes_data')
            ? $product->getDataFieldConfig('configurable_attributes_data')['source']->getAttributesData()
            : null;
        if ($attributesData == null) {
            return $result;
        }
        foreach ($checkoutData['options']['configurable_options'] as $option) {
            $attributeId = $attributesData[$option['title']]['attribute_id'];
            $optionId = $attributesData[$option['title']]['options'][$option['value']]['id'];
            $result['super_attribute'][$attributeId] = $optionId;
        }

        return $result;
    }

    /**
     * Prepare data for bundle product.
     *
     * @param BundleProduct $product
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function prepareBundleData(BundleProduct $product)
    {
        $result = [];
        $checkoutData = $product->getCheckoutData();
        $bundleOptions = isset($checkoutData['options']['bundle_options'])
            ? $checkoutData['options']['bundle_options']
            : [];
        $bundleSelections = $product->getBundleSelections();
        $bundleSelectionsData = [];
        $result['qty'] = $checkoutData['qty'];

        foreach ($bundleSelections['bundle_options'] as $option) {
            foreach ($option['assigned_products'] as $productData) {
                $productName = $productData['search_data']['name'];
                $bundleSelectionsData[$productName] = [
                    'selection_id' => $productData['selection_id'],
                    'option_id' => $productData['option_id'],
                ];
            }
        }

        foreach ($bundleOptions as $option) {
            $productName = $option['value']['name'];
            foreach ($bundleSelectionsData as $fullProductName => $value) {
                if (null !== strpos($fullProductName, $productName)) {
                    $productName = $fullProductName;
                }
            }

            if (isset($bundleSelectionsData[$productName])) {
                $optionId = $bundleSelectionsData[$productName]['option_id'];
                $selectionId = $bundleSelectionsData[$productName]['selection_id'];
                $result['bundle_option'][$optionId] = $selectionId;
            }
        }

        return $result;
    }

    /**
     * Prepare data for downloadable product.
     *
     * @param DownloadableProduct $product
     * @return array
     */
    protected function prepareDownloadableData(DownloadableProduct $product)
    {
        $result = [];
        $checkoutData = $product->getCheckoutData();
        foreach ($checkoutData['options']['links'] as $link) {
            $result['links'][] = $link['id'];
        }

        return $result;
    }

    /**
     * Prepare data for simple product.
     *
     * @param FixtureInterface $product
     * @return array
     */
    protected function prepareSimpleData(FixtureInterface $product)
    {
        return ['qty' => $product->getCheckoutData()['qty']];
    }

    /**
     * Prepare order data.
     *
     * @param array $data
     * @return array
     */
    protected function prepareOrderData(array $data)
    {
        $customerGroupId = $this->customer->hasData('group_id')
            ? $this->customer->getDataFieldConfig('group_id')['source']->getCustomerGroup()->getCustomerGroupId()
            : 1;
        $result = [
            'name' => $this->customer->getFirstname(),
            'order' => [
                'currency' => $data['order_currency_code'],
                'account' => [
                    'group_id' => $customerGroupId,
                    'email' => $this->customer->getEmail(),
                ],
                'shipping_method' => isset($data['shipping_method']) ? $data['shipping_method'] : '',
            ],
            'item' => $this->prepareOrderProductsData($data['entity_id']),
            'billing_address' => $this->prepareBillingAddress($data['billing_address_id']),
            'shipping_same_as_billing' => 'on',
            'payment' => $data['payment_auth_expiration'],

        ];

        return $result;
    }

    /**
     * Prepare customer data.
     *
     * @param array $data
     * @return array
     */
    protected function prepareCustomerData(array $data)
    {
        return [
            'currency_id' => $data['base_currency_code'],
            'customer_id' => $this->customer->getData('id'),
            'payment' => $data['payment_authorization_amount'],
            'store_id' => $this->order->getDataFieldConfig('store_id')['source']->store->getStoreId()
        ];
    }

    /**
     * Prepare order products data.
     *
     * @param array $data
     * @return array
     */
    protected function prepareOrderProductsData(array $data)
    {
        $result = [];
        foreach ($data['products'] as $product) {
            if (isset($product->getCheckoutData()['qty'])) {
                $result[$product->getId()] = ['qty' => ['qty' => $product->getCheckoutData()['qty']]];
            }
        }

        return $result;
    }

    /**
     * Prepare billing address data.
     *
     * @param array $data
     * @return array
     */
    protected function prepareBillingAddress(array $data)
    {
        $result = $data;
        $result['firstname'] = $this->customer->getFirstname();
        $result['lastname'] = $this->customer->getLastname();

        return $result;
    }

    /**
     * Create product via curl.
     *
     * @param array $data
     * @return int|null
     * @throws \Exception
     */
    protected function createOrder(array $data)
    {
        $curl = new BackendDecorator(new CurlTransport(), $this->_configuration);
        foreach ($this->steps as $key => $step) {
            if (!isset($data[$key])) {
                continue;
            }
            $url = $_ENV['app_backend_url'] . 'sales/order_create/loadBlock/block/' . $step . '?isAjax=true';
            $curl->write($url, $data[$key]);
            $curl->read();
        }
        $url = $_ENV['app_backend_url'] . 'sales/order_create/save';
        $curl->addOption(CURLOPT_HEADER, 1);
        $curl->write($url, $data['order_data']);
        $response = $curl->read();
        $curl->close();

        if (!strpos($response, 'data-ui-id="messages-message-success"')) {
            throw new \Exception("Order creation by curl handler was not successful! Response: $response");
        }
        preg_match("~<h1 class=\"page-title\">#(.*)</h1>~", $response, $matches);

        return isset($matches[1]) ? $matches[1] : null;
    }
}
