<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Handler\OrderInjectable;

use Magento\Bundle\Test\Fixture\BundleProduct;
use Magento\ConfigurableProduct\Test\Fixture\ConfigurableProduct;
use Magento\Downloadable\Test\Fixture\DownloadableProduct;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Util\Protocol\CurlTransport;
use Magento\Mtf\Handler\Webapi as AbstractWebapi;
use Magento\Mtf\Util\Protocol\CurlTransport\WebapiDecorator;

/**
 * Create new order via web API.
 */
class Webapi extends AbstractWebapi implements OrderInjectableInterface
{
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
            'United Kingdom' => 'GB',
        ],
    ];

    /**
     * Order quote value.
     *
     * @var string
     */
    protected $quote;

    /**
     * First part of Web API url for creating order.
     *
     * @var string
     */
    protected $url;

    /**
     * Either customer is a guest or not.
     *
     * @var bool
     */
    private $isCustomerGuest;

    /**
     * Creating order using quote via web API.
     *
     * @param FixtureInterface|null $fixture [optional]
     * @return array
     */
    public function persist(FixtureInterface $fixture = null)
    {
        $this->isCustomerGuest = $fixture->getData('customer_id') ? false : true;

        /** @var OrderInjectable $fixture */
        $this->createQuote($fixture);
        $url = $this->isCustomerGuest ? 'guest-carts/' . $this->quote  : 'carts/' . (int)$this->quote;
        $this->url = $_ENV['app_frontend_url'] . $this->prepareWebsiteUrl($fixture) . '/V1/' . $url;

        $this->setProducts($fixture);
        $this->setCoupon($fixture);
        $this->setBillingAddress($fixture);
        $this->setShippingInformation($fixture);
        $this->setPaymentMethod($fixture);
        $orderId = $this->placeOrder();
        $orderIncrementId = $this->getOrderIncrementId($orderId);

        return ['id' => $orderIncrementId];
    }

    /**
     * Create checkout quote.
     *
     * @param OrderInjectable $order
     * @return void
     * @throws \Exception
     */
    protected function createQuote(OrderInjectable $order)
    {
        if ($this->isCustomerGuest) {
            $url = $_ENV['app_frontend_url'] . $this->prepareWebsiteUrl($order) . '/V1/guest-carts';
            $this->webapiTransport->write($url);
            $response = json_decode($this->webapiTransport->read(), true);
            $this->webapiTransport->close();
            if (!is_string($response)) {
                $this->eventManager->dispatchEvent(['webapi_failed'], [$response]);
                throw new \Exception('Could not create a checkout quote using web API.');
            }
            $this->quote = $response;
        } else {
            $url = $_ENV['app_frontend_url'] . $this->prepareWebsiteUrl($order)
                . '/V1/customers/' . $order->getCustomerId()->getId() . '/carts';
            $data = '{"customerId": "' . $order->getCustomerId()->getId() . '"}';
            $this->webapiTransport->write($url, $data);
            $response = json_decode($this->webapiTransport->read(), true);
            $this->webapiTransport->close();
            if (!is_numeric($response)) {
                $this->eventManager->dispatchEvent(['webapi_failed'], [$response]);
                throw new \Exception('Could not create a checkout quote using web API.');
            }
            $this->quote = $response;
        }
    }

    /**
     * Add products to quote.
     *
     * @param OrderInjectable $order
     * @return void
     * @throws \Exception
     */
    protected function setProducts(OrderInjectable $order)
    {
        $url = $this->url . '/items';
        $products = $order->getEntityId()['products'];
        foreach ($products as $product) {
            $data = [
                'cartItem' => [
                    'sku' => $product->getSku(),
                    'qty' => isset($product->getCheckoutData()['qty']) ? $product->getCheckoutData()['qty'] : 1,
                    'quote_id' => $this->quote
                ]
            ];
            $methodName = 'prepare' . ucfirst($product->getDataConfig()['type_id']) . 'Options';
            if (method_exists($this, $methodName)) {
                $data['cartItem']['product_option'] = $this->$methodName($product);
            }
            $this->webapiTransport->write($url, $data);
            $response = (array)json_decode($this->webapiTransport->read(), true);
            $this->webapiTransport->close();
            if (isset($response['message'])) {
                $this->eventManager->dispatchEvent(['webapi_failed'], [$response]);
                throw new \Exception('Could not add product item to quote!');
            }
        }
    }

    /**
     * Set coupon to quote.
     *
     * @param OrderInjectable $order
     * @return void
     * @throws \Exception
     */
    protected function setCoupon(OrderInjectable $order)
    {
        if (!$order->hasData('coupon_code')) {
            return;
        }
        $url = $this->url . '/coupons/' . $order->getCouponCode()->getCouponCode();
        $data = [
            'cartId' => $this->quote,
            'couponCode' => $order->getCouponCode()->getCouponCode()
        ];
        $this->webapiTransport->write($url, $data, WebapiDecorator::PUT);
        $response = json_decode($this->webapiTransport->read(), true);
        $this->webapiTransport->close();
        if ($response !== true) {
            $this->eventManager->dispatchEvent(['webapi_failed'], [$response]);
            throw new \Exception('Could not apply coupon code!');
        }
    }

    /**
     * Set billing address to quote.
     *
     * @param OrderInjectable $order
     * @return void
     * @throws \Exception
     */
    protected function setBillingAddress(OrderInjectable $order)
    {
        $url = $this->url . "/billing-address";
        $address = $order->getBillingAddressId();

        unset($address['default_billing']);
        unset($address['default_shipping']);
        foreach (array_keys($this->mappingData) as $key) {
            if (isset($address[$key])) {
                $address[$key] = $this->mappingData[$key][$address[$key]];
            }
        }
        $data = ["address" => $address];
        $this->webapiTransport->write($url, $data);
        $response = json_decode($this->webapiTransport->read(), true);
        $this->webapiTransport->close();
        if (!is_numeric($response)) {
            $this->eventManager->dispatchEvent(['webapi_failed'], [$response]);
            throw new \Exception("Could not set billing addresss to quote!");
        }
    }

    /**
     * Set shipping information to quote
     *
     * @param OrderInjectable $order
     * @throws \Exception
     */
    protected function setShippingInformation(OrderInjectable $order)
    {
        if (!$order->hasData('shipping_method')) {
            return;
        }
        $url = $this->url . '/shipping-information';
        list($carrier, $method) = explode('_', $order->getShippingMethod());

        $address = $order->hasData('shipping_address_id')
            ? $order->getShippingAddressId()
            : $order->getBillingAddressId();

        unset($address['default_billing']);
        unset($address['default_shipping']);
        foreach (array_keys($this->mappingData) as $key) {
            if (isset($address[$key])) {
                $address[$key] = $this->mappingData[$key][$address[$key]];
            }
        }

        $data = [
            'addressInformation' => [
                'shippingAddress' => $address,
                'shippingMethodCode' => $method,
                'shippingCarrierCode' => $carrier,
            ]
        ];

        $this->webapiTransport->write($url, $data, WebapiDecorator::POST);
        $response = json_decode($this->webapiTransport->read(), true);
        $this->webapiTransport->close();
        if (!isset($response['payment_methods'], $response['totals'])) {
            $this->eventManager->dispatchEvent(['webapi_failed'], [$response]);
            throw new \Exception('Could not set shipping method to quote!');
        }
    }

    /**
     * Set payment method to quote.
     *
     * @param OrderInjectable $order
     * @return void
     * @throws \Exception
     */
    protected function setPaymentMethod(OrderInjectable $order)
    {
        $url = $this->url . '/selected-payment-method';
        $data = [
            "cartId" => $this->quote,
            "method" => $order->getPaymentAuthExpiration()
        ];
        $this->webapiTransport->write($url, $data, WebapiDecorator::PUT);
        $response = json_decode($this->webapiTransport->read(), true);
        $this->webapiTransport->close();
        if (!is_numeric($response)) {
            $this->eventManager->dispatchEvent(['webapi_failed'], [$response]);
            throw new \Exception('Could not set payment method to quote!');
        }
    }

    /**
     * Place order.
     *
     * @return array
     * @throws \Exception
     */
    protected function placeOrder()
    {
        $url = $this->url . '/order';
        $data = ["cartId" => $this->quote];
        $this->webapiTransport->write($url, $data, WebapiDecorator::PUT);
        $response = json_decode($this->webapiTransport->read(), true);
        $this->webapiTransport->close();
        if (!is_numeric($response)) {
            $this->eventManager->dispatchEvent(['webapi_failed'], [$response]);
            throw new \Exception('Could not place order via web API!');
        }

        return $response;
    }

    /**
     * Prepare configurable product options.
     *
     * @param ConfigurableProduct $product
     * @return array
     */
    protected function prepareConfigurableOptions(ConfigurableProduct $product)
    {
        $options = [];
        $attributesData = $product->getDataFieldConfig('configurable_attributes_data')['source']->getAttributesData();
        foreach ($product->getCheckoutData()['options']['configurable_options'] as $checkoutOption) {
            $options[] = [
                'option_id' => $attributesData[$checkoutOption['title']]['attribute_id'],
                'option_value' => $attributesData[$checkoutOption['title']]['options'][$checkoutOption['value']]['id'],
            ];
        }

        return ['extension_attributes' => ['configurable_item_options' => $options]];
    }

    /**
     * Prepare bundle product options.
     *
     * @param BundleProduct $product
     * @return array
     */
    protected function prepareBundleOptions(BundleProduct $product)
    {
        $options = [];
        foreach ($product->getCheckoutData()['options']['bundle_options'] as $checkoutOption) {
            foreach ($product->getBundleSelections()['bundle_options'] as $productOption) {
                if (strpos($productOption['title'], $checkoutOption['title']) !== false) {
                    $option = [];
                    foreach ($productOption['assigned_products'] as $productData) {
                        if (strpos($productData['search_data']['name'], $checkoutOption['value']['name']) !== false) {
                            $qty = isset($checkoutOption['qty'])
                                ? $checkoutOption['qty']
                                : $productData['data']['selection_qty'];
                            $option['option_id'] = $productData['option_id'];
                            $option['option_selections'][] = $productData['selection_id'];
                            $option['option_qty'] = $qty;
                        }
                    }
                    $options[] = $option;
                }
            }
        }

        return ['extension_attributes' => ['bundle_options' => $options]];
    }

    /**
     * Prepare downloadable product options.
     *
     * @param DownloadableProduct $product
     * @return array
     */
    protected function prepareDownloadableOptions(DownloadableProduct $product)
    {
        $checkoutData = $product->getCheckoutData();
        $links = [];
        foreach ($checkoutData['options']['links'] as $link) {
            $links[] = $link['id'];
        }

        return ['extension_attributes' => ['downloadable_option' => ['downloadable_links' => $links]]];
    }

    /**
     * Prepare url for placing order in custom website.
     *
     * @param OrderInjectable $order
     * @return string
     */
    private function prepareWebsiteUrl(OrderInjectable $order)
    {
        $url = 'rest';
        if ($website = $order->getDataFieldConfig('store_id')['source']->getWebsite()) {
            $store = $order->getDataFieldConfig('store_id')['source']->getStore();
            $url = 'websites/' . $website->getCode() . '/rest/' . $store->getCode();
        }
        return $url;
    }

    /**
     * Retrieve order increment id.
     *
     * @param int $orderId
     * @return string
     * @throws \Exception
     */
    private function getOrderIncrementId($orderId)
    {
        $url = $_ENV['app_frontend_url'] . 'rest/V1/orders/' . $orderId;
        $this->webapiTransport->write($url, [], WebapiDecorator::GET);
        $response = json_decode($this->webapiTransport->read(), true);
        $this->webapiTransport->close();

        if (!$response['increment_id']) {
            $this->eventManager->dispatchEvent(['webapi_failed'], [$response]);
            throw new \Exception('Could not get order details using web API.');
        }
        return $response['increment_id'];
    }
}
