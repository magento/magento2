<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\Handler\CatalogRule;

use Magento\Backend\Test\Handler\Conditions;
use Magento\CatalogRule\Test\Handler\CatalogRule;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Util\Protocol\CurlInterface;
use Magento\Mtf\Util\Protocol\CurlTransport;
use Magento\Mtf\Util\Protocol\CurlTransport\BackendDecorator;

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
            'type' => \Magento\CatalogRule\Model\Rule\Condition\Combine::class,
            'aggregator' => 'all',
            'value' => 1,
        ],
        'Category' => [
            'type' => \Magento\CatalogRule\Model\Rule\Condition\Product::class,
            'attribute' => 'category_ids',
        ],
        'Attribute' => [
            'type' => \Magento\CatalogRule\Model\Rule\Condition\Product::class,
            'attribute' => 'attribute_id',
        ],
    ];

    /**
     * Mapping values for data.
     *
     * @var array
     */
    protected $mappingData = [
        'simple_action' => [
            'Apply as percentage of original' => 'by_percent',
            'Apply as fixed amount' => 'by_fixed',
            'Adjust final price to this percentage' => 'to_percent',
            'Adjust final price to discount value' => 'to_fixed',
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
        $curl = new BackendDecorator(new CurlTransport(), $this->_configuration);
        $curl->addOption(CURLOPT_HEADER, 1);
        $curl->write($url, $data);
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
        $curl = new BackendDecorator(new CurlTransport(), $this->_configuration);
        $curl->write($url, [], CurlInterface::GET);
        $response = $curl->read();
        $curl->close();

        $pattern = '/col\-rule_id[\s\W]*(\d+).*?' . $data['name'] . '/siu';
        preg_match($pattern, $response, $matches);
        if (empty($matches)) {
            throw new \Exception('Cannot find Catalog Price Rule id! Response: ' . $response);
        }

        return $matches[1];
    }
}
