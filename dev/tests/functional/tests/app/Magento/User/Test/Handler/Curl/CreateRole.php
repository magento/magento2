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

namespace Magento\User\Test\Handler\Curl;

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
class CreateRole extends Curl
{
    /**
     * Convert array from canonical to UI format
     *
     * @param array $fields
     * @return array
     */
    protected function _preparePostData(array $fields)
    {
        $data = array();
        foreach ($fields as $key => $value) {
            $data[$key] = $value['value'];
        }
        return $data;
    }

    /**
     * Look for role id on grid
     *
     * @param string $name
     * @param string $page
     * @return bool|string
     */
    protected function findRoleOnPage($name, $page)
    {
        $dom = new \DOMDocument();
        $dom->loadHTML($page);
        $xpath = new \DOMXPath($dom);
        $row = '//tr[td[@data-column="role_name" and contains(text(),"' . $name . '")]]';
        $nodes = $xpath->query($row . '/td[@data-column="role_id"]');
        if ($nodes->length == 0) {
            return false;
        }
        $node = $nodes->item(0);
        $id = trim($node->nodeValue);
        return $id;
    }

    /**
     * Send POST request with encoded filter parameters in URL
     *
     * @param $name
     * @return string
     */
    protected function filterByName($name)
    {
        $filter = base64_encode('role_name=' . $name);
        $url = $_ENV['app_backend_url'] . 'admin/user_role/roleGrid/filter/' . $filter . '/';
        $curl = new BackendDecorator(new CurlTransport(), new Config());
        $curl->write(CurlInterface::POST, $url, '1.0', array(), array());
        $response = $curl->read();
        $curl->close();
        return $response;
    }

    /**
     * Look for needed role name and define id by name,
     * if the role is absent on the page, filtering is performed
     *
     * @param string $name
     * @param string $response
     * @return bool|string
     * @throws \UnderflowException
     * @throws \Exception
     */
    protected function findIdWithFilter($name, $response)
    {
        preg_match('/<table[\ \w\"\=]+id\="roleGrid_table">.*?<\/table>/siu', $response, $matches);
        if (empty($matches)) {
            throw new \Exception('Cannot find grid in response');
        }
        $gridHtml = $matches[0];

        $id = $this->findRoleOnPage($name, $gridHtml);

        // maybe, role is on another page, let's filter
        if (false === $id) {
            $newPage = $this->filterByName($name);
            $id = $this->findRoleOnPage($name, $newPage);
        }

        // still not found?? It's very suspicious.
        if (false === $id) {
            throw new \UnderflowException('Role with name "' . $name . '" not found');
        }

        return $id;
    }

    /**
     * Execute handler
     *
     * @param FixtureInterface|null $fixture [optional]
     * @throws \UnexpectedValueException
     * @throws \UnderflowException from findIdWithFilter
     * @throws \Exception from findIdWithFilter
     * @return mixed
     */
    public function persist(FixtureInterface $fixture = null)
    {
        $url = $_ENV['app_backend_url'] . 'admin/user_role/saverole/';
        $data = $this->_preparePostData($fixture->getData('fields'));

        $curl = new BackendDecorator(new CurlTransport(), new Config());
        $curl->write(CurlInterface::POST, $url, '1.0', array(), $data);
        $response = $curl->read();
        $curl->close();

        if (!strpos($response, 'data-ui-id="messages-message-success"')) {
            throw new \UnexpectedValueException('Success confirmation not found');
        }

        $data['id'] = $this->findIdWithFilter($data['rolename'], $response);
        return $data;
    }
}
