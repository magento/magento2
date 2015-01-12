<?php
/**
 * @spi
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Test\Handler\Curl;

use Mtf\Fixture\FixtureInterface;
use Mtf\Handler\Curl;
use Mtf\System\Config;
use Mtf\Util\Protocol\CurlInterface;
use Mtf\Util\Protocol\CurlTransport;
use Mtf\Util\Protocol\CurlTransport\BackendDecorator;

/**
 * Class CreateBundle
 * Curl handler for creating bundle product.
 */
class CreateBundle extends Curl
{
    /**
     * Mapping values for data.
     *
     * @var array
     */
    protected $mappingData = [
        'selection_can_change_qty' => [
            'Yes' => 1,
            'No' => 0,
        ],
        'required' => [
            'Yes' => 1,
            'No' => 0,
        ],
        'sku_type' => [
            'Dynamic' => 0,
            'Fixed' => 1,
        ],
        'price_type' => [
            'Dynamic' => 0,
            'Fixed' => 1,
        ],
        'weight_type' => [
            'Dynamic' => 0,
            'Fixed' => 1,
        ],
        'shipment_type' => [
            'Together' => 0,
            'Separately' => 1,
        ],
        'type' => [
            'Drop-down' => 'select',
            'Radio Buttons' => 'radio',
            'Checkbox' => 'checkbox',
            'Multiple Select' => 'multi',
        ],
        'selection_price_type' => [
            'Fixed' => 0,
            'Percent' => 1,
        ],
    ];

    /**
     * Prepare POST data for creating bundle product request
     *
     * @param array $params
     * @param string|null $prefix [optional]
     * @return array
     */
    protected function _prepareData(array $params, $prefix = null)
    {
        $data = [];
        foreach ($params as $key => $values) {
            if ($key == 'bundle_selections') {
                $data = array_merge($data, $this->_getBundleData($values['value']));
            } else {
                $value = $this->_getValue($values);
                // do not add this data if value does not exist
                if (null === $value) {
                    continue;
                }
                if (isset($values['input_name'])) {
                    $key = $values['input_name'];
                }
                if ($prefix) {
                    $data[$prefix][$key] = $value;
                } else {
                    $data[$key] = $value;
                }
            }
        }

        return $data;
    }

    /**
     * Retrieve field value or return null if value does not exist
     *
     * @param array $values
     * @return null|mixed
     */
    protected function _getValue(array $values)
    {
        if (!isset($values['value'])) {
            return null;
        }
        return isset($values['input_value']) ? $values['input_value'] : $values['value'];
    }

    /**
     * Prepare bundle specific data
     *
     * @param array $params
     * @return array
     */
    protected function _getBundleData(array $params)
    {
        $data = [
            'bundle_options' => [],
            'bundle_selections' => [],
        ];
        $index = 0;
        foreach ($params['bundle_options'] as $option) {
            $data['bundle_options'][] = [
                'title' => $option['title'],
                'type' => $option['type'],
                'required' => $option['required'],
                'delete' => '',
                'position' => $index,
            ];

            $position = 0;
            foreach ($option['assigned_products'] as $assignedProduct) {
                $assignedProduct['data'] += [
                    'delete' => '',
                    'position' => ++$position
                ];
                $data['bundle_selections'][$index][] = $assignedProduct['data'];
            }
            ++$index;
        }

        return $this->replaceMappingData($data);
    }

    /**
     * Retrieve URL for request with all necessary parameters
     *
     * @param array $config
     * @return string
     */
    protected function _getUrl(array $config)
    {
        $requestParams = isset($config['create_url_params']) ? $config['create_url_params'] : [];
        $params = '';
        foreach ($requestParams as $key => $value) {
            $params .= $key . '/' . $value . '/';
        }
        return $_ENV['app_backend_url'] . 'catalog/product/save/' . $params . 'popup/1/back/edit';
    }

    /**
     * Prepare product selections data
     *
     * @param array $products
     * @return array
     */
    protected function _getSelections(array $products)
    {
        $data = [];
        foreach ($products as $product) {
            $product = isset($product['data']) ? $product['data'] : [];
            $data[] = $this->_prepareData($product) + ['delete' => ''];
        }
        return $data;
    }

    /**
     * Post request for creating bundle product
     *
     * @param FixtureInterface|null $fixture [optional]
     * @return mixed|string
     * @throws \Exception
     */
    public function persist(FixtureInterface $fixture = null)
    {
        $config = $fixture->getDataConfig();

        $prefix = isset($config['input_prefix']) ? $config['input_prefix'] : null;
        $data = $this->_prepareData($fixture->getData('fields'), $prefix);
        if ($fixture->getData('category_id')) {
            $data['product']['category_ids'] = $fixture->getData('category_id');
        }
        $url = $this->_getUrl($config);
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
}
