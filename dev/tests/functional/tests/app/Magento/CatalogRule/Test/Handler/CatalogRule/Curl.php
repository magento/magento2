<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\Handler\CatalogRule;

use Magento\Backend\Test\Handler\Conditions;
use Magento\CatalogRule\Test\Handler\CatalogRule;
use Mtf\Fixture\FixtureInterface;
use Mtf\System\Config;
use Mtf\Util\Protocol\CurlInterface;
use Mtf\Util\Protocol\CurlTransport;
use Mtf\Util\Protocol\CurlTransport\BackendDecorator;

/**
 * Class Curl
 * Curl that creates catalog price rule
 */
class Curl extends Conditions implements CatalogRuleInterface
{
    /**
     * Map of type parameter
     *
     * @var array
     */
    protected $mapTypeParams = [
        'Conditions combination' => [
            'type' => 'Magento\CatalogRule\Model\Rule\Condition\Combine',
            'aggregator' => 'all',
            'value' => 1,
        ],
        'Category' => [
            'type' => 'Magento\CatalogRule\Model\Rule\Condition\Product',
            'attribute' => 'category_ids',
        ],
    ];

    /**
     * Mapping values for data.
     *
     * @var array
     */
    protected $mappingData = [
        'simple_action' => [
            'By Percentage of the Original Price' => 'by_percent',
            'By Fixed Amount' => 'by_fixed',
            'To Percentage of the Original Price' => 'to_percent',
            'To Fixed Amount' => 'to_fixed',
        ],
        'is_active' => [
            'Active' => 1,
            'Inactive' => 0,
        ],
        'stop_rules_processing' => [
            'Yes' => 1,
            'No' => 0,
        ],
    ];

    /**
     * Mapping values for Websites
     *
     * @var array
     */
    protected $websiteIds = [
        'Main Website' => 1,
    ];

    /**
     * Mapping values for Customer Groups
     *
     * @var array
     */
    protected $customerGroupIds = [
        'NOT LOGGED IN' => 0,
        'General' => 1,
        'Wholesale' => 2,
        'Retailer' => 3,
    ];

    /**
     * POST request for creating Catalog Price Rule
     *
     * @param FixtureInterface $fixture
     * @return mixed|void
     * @throws \Exception
     */
    public function persist(FixtureInterface $fixture = null)
    {
        $data = $this->prepareData($fixture);
        $url = $_ENV['app_backend_url'] . 'catalog_rule/promo_catalog/save/';
        $curl = new BackendDecorator(new CurlTransport(), new Config());
        $curl->addOption(CURLOPT_HEADER, 1);
        $curl->write(CurlInterface::POST, $url, '1.0', [], $data);
        $response = $curl->read();
        $curl->close();

        if (!strpos($response, 'data-ui-id="messages-message-success"')) {
            throw new \Exception(
                "Catalog Price Rule entity creating by curl handler was not successful! Response: $response"
            );
        }

        return ['id' => $this->getCategoryPriceRuleId($data)];
    }

    /**
     * Prepare data from text to values
     *
     * @param FixtureInterface $fixture
     * @return array
     */
    protected function prepareData($fixture)
    {
        $data = $this->replaceMappingData($fixture->getData());
        if (isset($data['website_ids'])) {
            $websiteIds = [];
            foreach ($data['website_ids'] as $websiteId) {
                $websiteIds[] = isset($this->websiteIds[$websiteId]) ? $this->websiteIds[$websiteId] : $websiteId;
            }
            $data['website_ids'] = $websiteIds;
        }
        if (isset($data['customer_group_ids'])) {
            $customerGroupIds = [];
            foreach ($data['customer_group_ids'] as $customerGroupId) {
                $customerGroupIds[] = isset($this->customerGroupIds[$customerGroupId])
                    ? $this->customerGroupIds[$customerGroupId]
                    : $customerGroupId;
            }
            $data['customer_group_ids'] = $customerGroupIds;
        }
        if (!isset($data['stop_rules_processing'])) {
            $data['stop_rules_processing'] = 0;
        }

        if (!isset($data['rule'])) {
            $data['rule'] = null;
        }
        $data['rule'] = ['conditions' => $this->prepareCondition($data['rule'])];

        return $data;
    }

    /**
     * Get id after creating Category Price Rule
     *
     * @param array $data
     * @return mixed
     * @throws \Exception
     */
    public function getCategoryPriceRuleId(array $data)
    {
        // Sort data in grid to define category price rule id if more than 20 items in grid
        $url = $_ENV['app_backend_url'] . 'catalog_rule/promo_catalog/index/sort/rule_id/dir/desc';
        $curl = new BackendDecorator(new CurlTransport(), new Config());
        $curl->write(CurlInterface::POST, $url, '1.0');
        $response = $curl->read();
        $curl->close();

        $pattern = '/class=\" col\-id col\-rule_id\W*>\W+(\d+)\W+<\/td>\W+<td[\w\s\"=\-]*?>\W+?'
            . $data['name'] . '/siu';
        preg_match($pattern, $response, $matches);
        if (empty($matches)) {
            throw new \Exception('Cannot find Category Price Rule id');
        }

        return $matches[1];
    }
}
