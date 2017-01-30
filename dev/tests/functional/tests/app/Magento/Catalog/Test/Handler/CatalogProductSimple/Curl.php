<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Handler\CatalogProductSimple;

use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Util\Protocol\CurlTransport;
use Magento\Mtf\Handler\Curl as AbstractCurl;
use Magento\Mtf\Util\Protocol\CurlTransport\BackendDecorator;

/**
 * Create new simple product via curl.
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
        'product_has_weight' => [
            'Yes' => 1,
            'No' => 0,
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
        ],
        'msrp_display_actual_price_type' => [
            'Use config' => 0,
            'On Gesture' => 1,
            'In Cart' => 2,
            'Before Order Confirmation' => 3
        ],
        'enable_qty_increments' => [
            'Yes' => 1,
            'No' => 0,
        ],
    ];

    /**
     * Placeholder for price data sent Curl.
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
                'NOT LOGGED IN' => 0,
                'General' => 1
            ]
        ]
    ];

    /**
     * Placeholder for fpt data sent Curl
     *
     * @var array
     */
    protected $fptData = [
        'website' => [
            'name' => 'website_id',
            'data' => [
                'All Websites [USD]' => 0
            ]
        ],
        'country_name' => [
            'name' => 'country',
            'data' => [
                'United States' => 'US'
            ]
        ],
        'state_name' => [
            'name' => 'state',
            'data' => [
                'California' => 12,
                '*' => 0
            ]
        ]
    ];

    /**
     * Select custom options.
     *
     * @var array
     */
    protected $selectOptions = ['drop_down', 'radio', 'checkbox', 'multiple'];

    /**
     * Post request for creating simple product.
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

        return $this->createProduct($data, $config);
    }

    /**
     * Prepare POST data for creating product request.
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

        if (!isset($fields['status'])) {
            // Default product is enabled
            $fields['status'] = 1;
        }
        if (!isset($fields['visibility'])) {
            // Default product is visible on Catalog, Search
            $fields['visibility'] = 4;
        }

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
        if (isset($fields['fpt'])) {
            $attributeLabel = $fixture->getDataFieldConfig('attribute_set_id')['source']
                ->getAttributeSet()->getDataFieldConfig('assigned_attributes')['source']
                ->getAttributes()[0]->getFrontendLabel();
            $fields[$attributeLabel] = $this->prepareFptData($fields['fpt']);
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
        if (isset($fields['custom_attribute'])) {
            $fields[$fields['custom_attribute']['code']] = $fields['custom_attribute']['value'];
        }

        $fields = $this->prepareStockData($fields);
        $fields = $prefix ? [$prefix => $fields] : $fields;
        if ($isCustomOptions) {
            $fields['affect_product_custom_options'] = 1;
        }

        return $fields;
    }

    /**
     * Preparation of custom options data.
     *
     * @param array $fields
     * @return array
     */
    protected function prepareCustomOptionsData(array $fields)
    {
        $options = [];
        foreach ($fields['custom_options'] as $key => $customOption) {
            $options[$key] = [
                'is_delete' => '',
                'option_id' => 0,
                'type' => $this->optionNameConvert($customOption['type']),
            ];

            foreach ($customOption['options'] as $index => $option) {
                $customOption['options'][$index]['is_delete'] = '';
                $customOption['options'][$index]['price_type'] = strtolower($option['price_type']);
            }
            $options[$key] += in_array($options[$key]['type'], $this->selectOptions)
                ? ['values' => $customOption['options']]
                : $customOption['options'][0];

            unset($customOption['options']);
            $options[$key] += $customOption;
        }
        $fields['options'] = $options;
        unset($fields['custom_options']);

        return $fields;
    }

    /**
     * Convert option name.
     *
     * @param string $optionName
     * @return string
     */
    protected function optionNameConvert($optionName)
    {
        $optionName = substr($optionName, strpos($optionName, "/") + 1);
        $optionName = str_replace(['-', ' & '], "_", trim($optionName));
        $end = strpos($optionName, ' ');
        if ($end !== false) {
            $optionName = substr($optionName, 0, $end);
        }
        return strtolower($optionName);
    }

    /**
     * Preparation of stock data.
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
     * Preparation of tier price data.
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
     * Preparation of fpt data
     *
     * @param array $fields
     * @return array
     */
    protected function prepareFptData(array $fields)
    {
        foreach ($fields as &$field) {
            foreach ($this->fptData as $key => $data) {
                $field[$data['name']] = $this->fptData[$key]['data'][$field[$key]];
                unset($field[$key]);
            }
            $field['delete'] = '';
        }
        return $fields;
    }

    /**
     * Remove items from a null.
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
     * Create product via curl.
     *
     * @param array $data
     * @param array $config
     * @return array
     * @throws \Exception
     */
    protected function createProduct(array $data, array $config)
    {
        $config['create_url_params']['set'] = isset($data['product']['attribute_set_id'])
            ? $data['product']['attribute_set_id']
            : $config['create_url_params']['set'];
        $url = $this->getUrl($config);
        $curl = new BackendDecorator(new CurlTransport(), $this->_configuration);
        $curl->addOption(CURLOPT_HEADER, 1);
        $curl->write($url, $data);
        $response = $curl->read();
        $curl->close();

        if (!strpos($response, 'data-ui-id="messages-message-success"')) {
            $this->_eventManager->dispatchEvent(['curl_failed'], [$response]);
            throw new \Exception('Product creation by curl handler was not successful!');
        }

        return $this->parseResponse($response);
    }

    /**
     * Parse data in response.
     *
     * @param string $response
     * @return array
     */
    protected function parseResponse($response)
    {
        preg_match('~Location: [^\s]*\/id\/(\d+)~', $response, $matches);
        $id = isset($matches[1]) ? $matches[1] : null;
        return ['id' => $id];
    }

    /**
     * Retrieve URL for request with all necessary parameters.
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
