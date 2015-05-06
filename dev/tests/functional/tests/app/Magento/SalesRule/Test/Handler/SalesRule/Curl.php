<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Test\Handler\SalesRule;

use Magento\Backend\Test\Handler\Conditions;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Util\Protocol\CurlInterface;
use Magento\Mtf\Util\Protocol\CurlTransport;
use Magento\Mtf\Util\Protocol\CurlTransport\BackendDecorator;

/**
 * Curl handler for creating sales rule.
 */
class Curl extends Conditions implements SalesRuleInterface
{
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
        'Conditions combination' => [
            'type' => 'Magento\SalesRule\Model\Rule\Condition\Combine',
            'aggregator' => 'all',
            'value' => '1',
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
            'Active' => 1,
            'Inactive' => 0,
        ],
        'coupon_type' => [
            'No Coupon' => 1,
            'Specific Coupon' => 2,
            'Auto' => 3,
        ],
        'is_rss' => [
            'Yes' => 1,
            'No' => 2,
        ],
        'simple_action' => [
            'Percent of product price discount' => 'by_percent',
            'Fixed amount discount' => 'by_fixed',
            'Fixed amount discount for whole cart' => 'cart_fixed',
            'Buy X get Y free (discount amount is Y)' => 'buy_x_get_y',
        ],
        'apply_to_shipping' => [
            'Yes' => 1,
            'No' => 2,
        ],
        'stop_rules_processing' => [
            'Yes' => 1,
            'No' => 2,
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
        $url = $_ENV['app_backend_url'] . 'sales_rule/promo_quote/save/';
        $data = $this->replaceMappingData($fixture->getData());
        $data['rule'] = [];
        if (isset($data['conditions_serialized'])) {
            $data['rule']['conditions'] = $this->prepareCondition($data['conditions_serialized']);
            unset($data['conditions_serialized']);
        }

        $data['website_ids'] = $this->prepareWebsites($data);
        $data['customer_group_ids'] = $this->prepareCustomerGroup($data);

        if (isset($data['actions_serialized'])) {
            $this->mapTypeParams['Conditions combination']['type'] =
                'Magento\SalesRule\Model\Rule\Condition\Product\Combine';
            $data['rule']['actions'] = $this->prepareCondition($data['actions_serialized']);
            unset($data['actions_serialized']);
        }
        $curl = new BackendDecorator(new CurlTransport(), $this->_configuration);
        $curl->write(CurlInterface::POST, $url, '1.0', [], $data);
        $response = $curl->read();
        $curl->close();
        if (!strpos($response, 'data-ui-id="messages-message-success"')) {
            throw new \Exception("Sales rule entity creating by curl handler was not successful! Response: $response");
        }

        preg_match('`<tr.*title=".*sales_rule\/promo_quote\/edit\/id\/([\d]+)`ims', $response, $matches);
        if (empty($matches)) {
            throw new \Exception('Cannot find Sales Rule id');
        }

        return ['id' => $matches[1]];
    }

    /**
     * Prepare website data for curl.
     *
     * @param array $data
     * @return array
     */
    protected function prepareWebsites(array $data)
    {
        $websiteIds = [];
        if (!empty($data['website_ids'])) {
            foreach ($data['website_ids'] as $name) {
                $websiteIds[] = isset($this->websiteIds[$name]) ? $this->websiteIds[$name] : $name;
            }
        }

        return $websiteIds;
    }

    /**
     * Prepare customer group data for curl.
     *
     * @param array $data
     * @return array
     */
    protected function prepareCustomerGroup(array $data)
    {
        $groupIds = [];
        if (!empty($data['customer_group_ids'])) {
            foreach ($data['customer_group_ids'] as $name) {
                $groupIds[] = isset($this->customerIds[$name]) ? $this->customerIds[$name] : $name;
            }
        }

        return $groupIds;
    }
}
