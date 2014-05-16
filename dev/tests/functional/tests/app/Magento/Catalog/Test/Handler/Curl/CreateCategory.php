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
 * @spi
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Catalog\Test\Handler\Curl;

use Mtf\Fixture\FixtureInterface;
use Mtf\Handler\Curl;
use Mtf\Util\Protocol\CurlInterface;
use Mtf\Util\Protocol\CurlTransport;
use Mtf\Util\Protocol\CurlTransport\BackendDecorator;
use Mtf\System\Config;

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
        $data = array();
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
        $requestParams = isset($config['request_params']) ? $config['request_params'] : array();
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
        $curl->write(CurlInterface::POST, $url, '1.0', array(), $params);
        $response = $curl->read();
        $curl->close();

        preg_match("~.+\/id\/(\d+)\/.+~", $response, $matches);
        return isset($matches[1]) ? $matches[1] : null;
    }
}
