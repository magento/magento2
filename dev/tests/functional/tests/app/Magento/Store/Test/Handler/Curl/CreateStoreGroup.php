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

namespace Magento\Store\Test\Handler\Curl;

use Mtf\Fixture\FixtureInterface;
use Mtf\Handler\Curl;
use Mtf\Util\Protocol\CurlInterface;
use Mtf\Util\Protocol\CurlTransport;
use Mtf\Util\Protocol\CurlTransport\BackendDecorator;
use Mtf\System\Config;

/**
 * Curl handler for persisting Magento store group
 *
 */
class CreateStoreGroup extends Curl
{
    /**
     * Prepare POST data for creating store group request
     *
     * @param array $params
     * @return array
     */
    protected function _prepareData($params)
    {
        $data = array();
        foreach ($params['fields'] as $name => $config) {
            $data[$name] = $config['value'];
        }
        return $data;
    }

    /**
     * Get store id by store name
     *
     * @param string $storeName
     * @return int
     * @throws \UnexpectedValueException
     */
    protected function _getStoreGroupIdByGroupName($storeName)
    {
        //Set pager limit to 2000 in order to find created store group by name
        $url = $_ENV['app_backend_url'] . 'admin/system_store/index/sort/group_title/dir/asc/limit/2000';
        $curl = new BackendDecorator(new CurlTransport(), new Config);
        $curl->addOption(CURLOPT_HEADER, 1);
        $curl->write(CurlInterface::POST, $url, '1.0');
        $response = $curl->read();

        $expectedUrl = '/admin/system_store/editGroup/group_id/';
        $expectedUrl = preg_quote($expectedUrl);
        $expectedUrl = str_replace('/', '\/', $expectedUrl);
        preg_match('/' . $expectedUrl . '([0-9]*)\/(.)*>' . $storeName . '<\/a>/', $response, $matches);

        if (empty($matches)) {
            throw new \UnexpectedValueException('Cannot find store group id');
        }
        return intval($matches[1]);
    }

    /**
     * Post request for persisting Magento Store Group
     *
     * @param FixtureInterface $fixture
     * @return array
     * @throws \UnexpectedValueException
     * @throws \UnderflowException
     */
    public function persist(FixtureInterface $fixture = null)
    {
        $data = $this->_prepareData($fixture->getData());
        $data['group_id'] = '';
        $fields = array(
            'group' => $data,
            'store_action' => 'add',
            'store_type' => 'group',
        );

        $url = $_ENV['app_backend_url'] . 'admin/system_store/save/';
        $curl = new BackendDecorator(new CurlTransport(), new Config());
        $curl->write(CurlInterface::POST, $url, '1.0', array(), $fields);
        $response = $curl->read();
        $curl->close();

        if (!preg_match('/The store has been saved\./', $response)) {
            throw new \UnderflowException('Store group was\'t saved');
        }

        $data['id'] = $this->_getStoreGroupIdByGroupName($fixture->getData('fields/name/value'));

        return $data;
    }
}
