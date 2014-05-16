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

namespace Magento\User\Test\Handler\Curl;

use Mtf\Fixture\FixtureInterface;
use Mtf\Handler\Curl;
use Mtf\Util\Protocol\CurlTransport;
use Mtf\Util\Protocol\CurlInterface;
use Mtf\Util\Protocol\CurlTransport\BackendDecorator;
use Mtf\System\Config;

/**
 * Curl handler for persisting Magento user
 *
 */
class CreateUser extends Curl
{
    /**
     * Prepare data for using in the execute method
     *
     * @param array $fields
     * @return array
     */
    protected function _prepareData(array $fields)
    {
        $data = array();
        foreach ($fields as $key => $value) {
            $data[$key] = $value['value'];
        }
        return $data;
    }

    /**
     * Get id for newly created user
     *
     * @param $data
     * @return mixed
     * @throws \Exception
     */
    protected function _getUserId($data)
    {
        //Sort data in grid to define user id if more than 20 items in grid
        $url = $_ENV['app_backend_url'] . 'admin/user/roleGrid/sort/user_id/dir/desc';
        $curl = new BackendDecorator(new CurlTransport(), new Config);
        $curl->addOption(CURLOPT_HEADER, 1);
        $curl->write(CurlInterface::POST, $url, '1.0');
        $response = $curl->read();
        $curl->close();
        preg_match(
            '/class=\"\scol\-id col\-user_id\W*>\W+(\d+)\W+<\/td>\W+<td[\w\s\"=\-]*?>\W+?' . $data['username'] . '/siu',
            $response,
            $matches
        );
        if (empty($matches)) {
            throw new \Exception('Cannot find user id');
        }
        return $matches[1];
    }

    /**
     * Post request for creating user in backend
     *
     * @param FixtureInterface $fixture
     * @return array|mixed
     * @throws \Exception
     */
    public function persist(FixtureInterface $fixture = null)
    {
        $url = $_ENV['app_backend_url'] . 'admin/user/save';
        $data = $this->_prepareData($fixture->getData('fields'));
        $curl = new BackendDecorator(new CurlTransport(), new Config);
        $curl->addOption(CURLOPT_HEADER, 1);
        $curl->write(CurlInterface::POST, $url, '1.0', array(), $data);
        $response = $curl->read();
        $curl->close();
        if (!strpos($response, 'data-ui-id="messages-message-success"')) {
            throw new \Exception("User creation by curl handler was not successful! Response: $response");
        }
        //Sort data in grid to define user id if more than 20 items in grid
        $url = $_ENV['app_backend_url'] . 'admin/user/roleGrid/sort/user_id/dir/desc';
        $curl = new BackendDecorator(new CurlTransport(), new Config);
        $curl->addOption(CURLOPT_HEADER, 1);
        $curl->write(CurlInterface::POST, $url, '1.0', array(), $data);
        $response = $curl->read();
        $curl->close();
        $data['id'] = $this->_getUserId($data);
        return $data;

    }
}
