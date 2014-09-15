<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Catalog\Test\Handler\CatalogProductSimple;

use Mtf\System\Config;
use Mtf\Fixture\FixtureInterface;
use Mtf\Util\Protocol\CurlInterface;
use Mtf\Util\Protocol\CurlTransport;
use Mtf\Handler\Curl as AbstractCurl;
use Mtf\Util\Protocol\CurlTransport\BackendDecorator;

/**
 * Class CreateProduct
 * Create new simple product via curl
 */
class Curl extends AbstractCurl implements CatalogProductSimpleInterface
{
    /**
     * Mapping values for data.
     *
     * @var array
     */
    protected $mappingData = [
        'links_purchased_separately' => [
            'Yes' => 1,
            'No' => 0
        ],
        'use_config_notify_stock_qty' => [
            'Yes' => 1,
            'No' => 0
        ],
        'is_shareable' => [
            'Yes' => 1,
            'No' => 0,
            'Use config' => 2
        ],
        'required' => [
            'Yes' => 1,
            'No' => 0
        ],
        'manage_stock' => [
            'Yes' => 1,
            'No' => 0
        ],
        'use_config_manage_stock' => [
            'Yes' => 1,
            'No' => 0
        ],
        'is_virtual' => [
            'Yes' => 1
        ],
        'use_config_enable_qty_increments' => [
            'Yes' => 1,
            'No' => 0
        ],
        'use_config_qty_increments' => [
            'Yes' => 1,
            'No' => 0
        ],
        'is_in_stock' => [
            'In Stock' => 1,
            'Out of Stock' => 0
        ],
        'visibility' => [
            'Not Visible Individually' => 1,
            'Catalog' => 2,
            'Search' => 3,
            'Catalog, Search' => 4
        ],
        'website_ids' => [
            'Main Website' => 1
        ],
        'status' => [
            'Product offline' => 2,
            'Product online' => 1
        ],
        'is_require' => [
            'Yes' => 1,
            'No' => 0
        ]
    ];

    /**
     * Placeholder for price data sent Curl
     *
     * @var array
     */
    protected $priceData = [
        'website' => [
            'name' => 'website_id',
            'data' => [
                'All Websites [USD]' => 0
            ]
        ],
        'customer_group' => [
            'name' => 'cust_group',
            'data' => [
                'ALL GROUPS' => 32000,
                'NOT LOGGED IN' => 0
            ]
        ]
    ];

    /**
     * Select custom options
     *
     * @var array
     */
    protected $selectOptions = ['Drop-down', 'Radio Buttons', 'Checkbox', 'Multiple Select'];

    /**
     * Post request for creating simple product
     *
     * @param FixtureInterface|null $fixture [optional]
     * @return array
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function persist(FixtureInterface $fixture = null)
    {
        $config = $fixture->getDataConfig();
        $prefix = isset($config['input_prefix']) ? $config['input_prefix'] : null;
        $data = $this->prepareData($fixture, $prefix);

        return ['id' => $this->createProduct($data, $config)];
    }

    /**
     * Prepare POST data for creating product request
     *
     * @param FixtureInterface $fixture
     * @param string|null $prefix [optional]
     * @return array
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function prepareData(FixtureInterface $fixture, $prefix = null)
    {
        $fields = $this->replaceMappingData($fixture->getData());

        // Getting Tax class id
        if ($fixture->hasData('tax_class_id')) {
            $fields['tax_class_id'] = $fixture->getDataFieldConfig('tax_class_id')['source']->getTaxClassId();
        }

        if (!empty($fields['category_ids'])) {
            $categoryIds = [];
            foreach ($fixture->getDataFieldConfig('category_ids')['source']->getCategories() as $category) {
                $categoryIds[] = $category->getId();
            }
            $fields['category_ids'] = $categoryIds;
        }

        if (isset($fields['tier_price'])) {
            $fields['tier_price'] = $this->preparePriceData($fields['tier_price']);
        }
        if (isset($fields['group_price'])) {
            $fields['group_price'] = $this->preparePriceData($fields['group_price']);
        }
        if ($isCustomOptions = isset($fields['custom_options'])) {
            $fields = $this->prepareCustomOptionsData($fields);
        }

        if (!empty($fields['website_ids'])) {
            foreach ($fields['website_ids'] as &$value) {
                $value = isset($this->mappingData['website_ids'][$value])
                    ? $this->mappingData['website_ids'][$value]
                    : $value;
            }
        }

        // Getting Attribute Set id
        if ($fixture->hasData('attribute_set_id')) {
            $attributeSetId = $fixture
                ->getDataFieldConfig('attribute_set_id')['source']
                ->getAttributeSet()
                ->getAttributeSetId();
            $fields['attribute_set_id'] = $attributeSetId;
        }

        // Prepare assigned attribute
        if (isset($fields['attributes'])) {
            $fields += $fields['attributes'];
            unset($fields['attributes']);
        }

        $fields = $this->prepareStockData($fields);
        $fields = $prefix ? [$prefix => $fields] : $fields;
        if ($isCustomOptions) {
            $fields['affect_product_custom_options'] = 1;
        }

        return $fields;
    }

    /**
     * Preparation of custom options data
     *
     * @param array $fields
     * @return array
     */
    protected function prepareCustomOptionsData(array $fields)
    {
        $options = [];
        foreach ($fields['custom_options'] as $key => $customOption) {
            $options[$key] = ['option_id' => 0, 'is_delete' => ''];
            foreach ($customOption['options'] as $index => $option) {
                $customOption['options'][$index]['is_delete'] = '';
                $customOption['options'][$index]['price_type'] = strtolower($option['price_type']);
            }
            $options[$key] += in_array($customOption['type'], $this->selectOptions)
                ? ['values' => $customOption['options']]
                : $customOption['options'][0];
            unset($customOption['options']);
            $options[$key] += $customOption;
            $options[$key]['type'] = $this->optionNameConvert($customOption['type']);
        }
        $fields['options'] = $options;
        unset($fields['custom_options']);

        return $fields;
    }

    /**
     * Convert option name
     *
     * @param string $optionName
     * @return string
     */
    protected function optionNameConvert($optionName)
    {
        $optionName = str_replace(['-', ' & '], "_", trim($optionName));
        $end = strpos($optionName, ' ');
        if ($end !== false) {
            $optionName = substr($optionName, 0, $end);
        }
        return strtolower($optionName);
    }

    /**
     * Preparation of stock data
     *
     * @param array $fields
     * @return array
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function prepareStockData(array $fields)
    {
        if (isset($fields['quantity_and_stock_status']) && !is_array($fields['quantity_and_stock_status'])) {
            $fields['quantity_and_stock_status'] = [
                'qty' => $fields['qty'],
                'is_in_stock' => $fields['quantity_and_stock_status']
            ];
        }

        if (!isset($fields['stock_data']['is_in_stock'])) {
            $fields['stock_data']['is_in_stock'] = isset($fields['quantity_and_stock_status']['is_in_stock'])
                ? $fields['quantity_and_stock_status']['is_in_stock']
                : (isset($fields['inventory_manage_stock']) ? $fields['inventory_manage_stock'] : null);
        }
        if (!isset($fields['stock_data']['qty'])) {
            $fields['stock_data']['qty'] = isset($fields['quantity_and_stock_status']['qty'])
                ? $fields['quantity_and_stock_status']['qty']
                : null;
        }

        if (!isset($fields['stock_data']['manage_stock'])) {
            $fields['stock_data']['manage_stock'] = (int)(!empty($fields['stock_data']['qty'])
                || !empty($fields['stock_data']['is_in_stock']));
        }

        return $this->filter($fields);
    }

    /**
     * Preparation of tier price data
     *
     * @param array $fields
     * @return array
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function preparePriceData(array $fields)
    {
        foreach ($fields as &$field) {
            foreach ($this->priceData as $key => $data) {
                $field[$data['name']] = $this->priceData[$key]['data'][$field[$key]];
                unset($field[$key]);
            }
            $field['delete'] = '';
        }
        return $fields;
    }

    /**
     * Remove items from a null
     *
     * @param array $data
     * @return array
     */
    protected function filter(array $data)
    {
        foreach ($data as $key => $value) {
            if ($value === null) {
                unset($data[$key]);
            } elseif (is_array($data[$key])) {
                $data[$key] = $this->filter($data[$key]);
            }
        }
        return $data;
    }

    /**
     * Create product via curl
     *
     * @param array $data
     * @param array $config
     * @return int|null
     * @throws \Exception
     */
    protected function createProduct(array $data, array $config)
    {
        $url = $this->getUrl($config);
        $curl = new BackendDecorator(new CurlTransport(), new Config());
        $curl->addOption(CURLOPT_HEADER, 1);
        $curl->write(CurlInterface::POST, $url, '1.0', [], $data);
        $response = $curl->read();
        $curl->close();

        if (!strpos($response, 'data-ui-id="messages-message-success"')) {
            throw new \Exception("Product creation by curl handler was not successful! Response: $response");
        }
        preg_match("~Location: [^\s]*\/id\/(\d+)~", $response, $matches);

        return isset($matches[1]) ? $matches[1] : null;
    }

    /**
     * Retrieve URL for request with all necessary parameters
     *
     * @param array $config
     * @return string
     */
    protected function getUrl(array $config)
    {
        $requestParams = isset($config['create_url_params']) ? $config['create_url_params'] : [];
        $params = '';
        foreach ($requestParams as $key => $value) {
            $params .= $key . '/' . $value . '/';
        }

        return $_ENV['app_backend_url'] . 'catalog/product/save/' . $params . 'popup/1/back/edit';
    }
}
