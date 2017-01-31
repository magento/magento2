<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Test\Handler\SalesRule;

use Magento\Backend\Test\Handler\Conditions;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Util\Protocol\CurlTransport;
use Magento\Mtf\Util\Protocol\CurlTransport\BackendDecorator;
use Magento\SalesRule\Test\Fixture\SalesRule;

/**
 * Curl handler for creating sales rule.
 */
class Curl extends Conditions implements SalesRuleInterface
{
    /**
     * Sales rule instance.
     *
     * @var SalesRule
     */
    protected $fixture;

    /**
     * Prepared data for request to create sales rule.
     *
     * @var array
     */
    protected $data;

    /**
     * Map of type parameter.
     *
     * @var array
     */
    protected $mapTypeParams = [
        'Subtotal' => [
            'type' => 'Magento\SalesRule\Model\Rule\Condition\Address',
            'attribute' => 'base_subtotal',
        ],
        'Total Items Quantity' => [
            'type' => 'Magento\SalesRule\Model\Rule\Condition\Address',
            'attribute' => 'total_qty',
        ],
        'Conditions combination' => [
            'type' => 'Magento\SalesRule\Model\Rule\Condition\Combine',
            'aggregator' => 'all',
            'value' => '1',
        ],
        'Products subselection' => [
            'type' => 'Magento\SalesRule\Model\Rule\Condition\Product\Subselect',
            'attribute' => 'qty',
            'operator' => '==',
            'value' => '1',
            'aggregator' => 'all',
        ],
        'Product attribute combination' => [
            'type' => 'Magento\SalesRule\Model\Rule\Condition\Product\Found',
            'value' => '1',
            'aggregator' => 'all',
        ],
        'Shipping Country' => [
            'type' => 'Magento\SalesRule\Model\Rule\Condition\Address',
            'attribute' => 'country_id',
        ],
        'Shipping Postcode' => [
            'type' => 'Magento\SalesRule\Model\Rule\Condition\Address',
            'attribute' => 'postcode',
        ],
        'Category' => [
            'type' => 'Magento\SalesRule\Model\Rule\Condition\Product',
            'attribute' => 'category_ids',
        ],
        'Price in cart' => [
            'type' => 'Magento\SalesRule\Model\Rule\Condition\Product',
            'attribute' => 'quote_item_price',
        ],
        'Quantity in cart' => [
            'type' => 'Magento\SalesRule\Model\Rule\Condition\Product',
            'attribute' => 'quote_item_qty',
        ],
        'Row total in cart' => [
            'type' => 'Magento\SalesRule\Model\Rule\Condition\Product',
            'attribute' => 'quote_item_row_total',
        ]
    ];

    /**
     * Map of type additional parameter.
     *
     * @var array
     */
    protected $additionalMapTypeParams = [];

    /**
     * Mapping values for data.
     *
     * @var array
     */
    protected $mappingData = [
        'is_active' => [
            'Yes' => 1,
            'No' => 0,
        ],
        'coupon_type' => [
            'No Coupon' => 1,
            'Specific Coupon' => 2,
            'Auto' => 3,
        ],
        'is_rss' => [
            'Yes' => 1,
            'No' => 0,
        ],
        'simple_action' => [
            'Percent of product price discount' => 'by_percent',
            'Fixed amount discount' => 'by_fixed',
            'Fixed amount discount for whole cart' => 'cart_fixed',
            'Buy X get Y free (discount amount is Y)' => 'buy_x_get_y',
        ],
        'apply_to_shipping' => [
            'Yes' => 1,
            'No' => 0,
        ],
        'stop_rules_processing' => [
            'Yes' => 1,
            'No' => 0,
        ],
        'simple_free_shipping' => [
            'No' => 0,
            'For matching items only' => 1,
            'For shipment with matching items' => 2,
        ],
    ];

    /**
     * Mapping values for Websites.
     *
     * @var array
     */
    protected $websiteIds = [
        'Main Website' => 1,
    ];

    /**
     * Mapping values for customer group.
     *
     * @var array
     */
    protected $customerIds = [
        'NOT LOGGED IN' => 0,
        'General' => 1,
        'Wholesale' => 2,
        'Retailer' => 3,
    ];

    /**
     * Post request for creating sales rule.
     *
     * @param FixtureInterface $fixture
     * @return array
     * @throws \Exception
     */
    public function persist(FixtureInterface $fixture = null)
    {
        $this->mapTypeParams = array_merge($this->mapTypeParams, $this->additionalMapTypeParams);

        $data = $this->prepareData($fixture);
        $url = $_ENV['app_backend_url'] . 'sales_rule/promo_quote/save/';
        $curl = new BackendDecorator(new CurlTransport(), $this->_configuration);
        $curl->write($url, $data);
        $response = $curl->read();
        $curl->close();
        if (!strpos($response, 'data-ui-id="messages-message-success"')) {
            throw new \Exception("Sales rule entity creating by curl handler was not successful! Response: $response");
        }

        preg_match('`<tr.*title=".*sales_rule\/promo_quote\/edit\/id\/([\d]+)`ims', $response, $matches);
        if (empty($matches)) {
            throw new \Exception('Cannot find Sales Rule id');
        }

        return ['rule_id' => $matches[1]];
    }

    /**
     * Prepare data for creating sales rule request.
     *
     * @param FixtureInterface $fixture
     * @return array
     */
    public function prepareData(FixtureInterface $fixture)
    {
        $this->fixture = $fixture;
        $this->data = $this->replaceMappingData($this->fixture->getData());

        $this->data['rule'] = [];
        if (isset($this->data['conditions_serialized'])) {
            $this->data['rule']['conditions'] = $this->prepareCondition($this->data['conditions_serialized']);
            unset($this->data['conditions_serialized']);
        }

        $this->prepareWebsites();
        $this->prepareCustomerGroup();

        if (isset($this->data['actions_serialized'])) {
            $this->mapTypeParams['Conditions combination']['type'] =
                'Magento\SalesRule\Model\Rule\Condition\Product\Combine';
            $this->data['rule']['actions'] = $this->prepareCondition($this->data['actions_serialized']);
            unset($this->data['actions_serialized']);
        }

        return $this->data;
    }

    /**
     * Prepare website data for curl.
     *
     * @return array
     */
    protected function prepareWebsites()
    {
        $websiteIds = [];
        if (!empty($this->data['website_ids'])) {
            foreach ($this->data['website_ids'] as $name) {
                $websiteIds[] = isset($this->websiteIds[$name]) ? $this->websiteIds[$name] : $name;
            }
        }

        $this->data['website_ids'] = $websiteIds;
    }

    /**
     * Prepare customer group data for curl.
     *
     * @return array
     */
    protected function prepareCustomerGroup()
    {
        $groupIds = [];
        if (!empty($this->data['customer_group_ids'])) {
            foreach ($this->data['customer_group_ids'] as $name) {
                $groupIds[] = isset($this->customerIds[$name]) ? $this->customerIds[$name] : $name;
            }
        }

        $this->data['customer_group_ids'] = $groupIds;
    }
}
