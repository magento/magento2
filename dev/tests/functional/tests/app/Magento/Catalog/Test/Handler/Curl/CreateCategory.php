<?php
/**
 * @spi
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Handler\Curl;

use Mtf\Fixture\FixtureInterface;
use Mtf\Handler\Curl;
use Mtf\System\Config;
use Mtf\Util\Protocol\CurlInterface;
use Mtf\Util\Protocol\CurlTransport;
use Mtf\Util\Protocol\CurlTransport\BackendDecorator;

/**
 * Class CreateCategory.
 * Curl handler for creating category.
 *
 */
class CreateCategory extends Curl
{
    /**
     * Prepare POST data for creating category request
     *
     * @param array $fields
     * @param string|null $prefix
     * @return array
     */
    protected function _prepareData(array $fields, $prefix = null)
    {
        $data = [];
        foreach ($fields as $key => $values) {
            $value = $this->_getValue($values);
            //do not add this data if value does not exist
            if (null === $value) {
                continue;
            }
            if (isset($values['input_name'])) {
                $data[$values['input_name']] = $value;
            } elseif ($prefix) {
                $data[$prefix][$key] = $value;
            } else {
                $data[$key] = $value;
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
    protected function _getValue($values)
    {
        if (!isset($values['value'])) {
            return null;
        }
        return isset($values['input_value']) ? $values['input_value'] : $values['value'];
    }

    /**
     * Retrieve URL for request with all necessary parameters
     *
     * @param array $config
     * @param string|null $parentCategory
     * @return string
     */
    protected function _getUrl(array $config, $parentCategory)
    {
        $requestParams = isset($config['request_params']) ? $config['request_params'] : [];
        $params = '';
        foreach ($requestParams as $key => $value) {
            $params .= $key . '/' . $value . '/';
        }
        $params .= 'parent/' . (int)$parentCategory . '/';
        return $_ENV['app_backend_url'] . 'catalog/category/save/' . $params;
    }

    /**
     * Create category
     *
     * @param FixtureInterface $fixture [optional]
     * @return int|null
     */
    public function persist(FixtureInterface $fixture = null)
    {
        $config = $fixture->getDataConfig();
        $parentCategory = $fixture->getData('category_path/input_value');
        $url = $this->_getUrl($config, $parentCategory);

        $prefix = isset($config['input_prefix']) ? $config['input_prefix'] : null;
        $params = $this->_prepareData($fixture->getData('fields'), $prefix);

        $curl = new BackendDecorator(new CurlTransport(), new Config());
        $curl->write(CurlInterface::POST, $url, '1.0', [], $params);
        $response = $curl->read();
        $curl->close();

        preg_match("~.+\/id\/(\d+)\/.+~", $response, $matches);
        return isset($matches[1]) ? $matches[1] : null;
    }
}
