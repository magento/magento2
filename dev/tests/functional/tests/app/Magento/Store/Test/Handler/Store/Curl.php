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

namespace Magento\Store\Test\Handler\Store;

use Mtf\System\Config;
use Mtf\Fixture\FixtureInterface;
use Mtf\Util\Protocol\CurlInterface;
use Mtf\Util\Protocol\CurlTransport;
use Mtf\Util\Protocol\CurlTransport\BackendDecorator;
use Mtf\Handler\Curl as AbstractCurl;

/**
 * Class Curl
 * Curl handler for creating Store view
 */
class Curl extends AbstractCurl implements StoreInterface
{
    /**
     * Url for saving data
     *
     * @var string
     */
    protected $saveUrl = 'admin/system_store/save';

    /**
     * Mapping values for data
     *
     * @var array
     */
    protected $mappingData = [
        'group_id' => [
            'Main Website Store' => 1
        ],
        'is_active' => [
            'Enabled' => 1,
            'Disabled' => 0
        ],
    ];

    /**
     * POST request for creating store
     *
     * @param FixtureInterface|null $fixture [optional]
     * @return array
     * @throws \Exception
     */
    public function persist(FixtureInterface $fixture = null)
    {
        $data = $this->prepareData($fixture);
        $url = $_ENV['app_backend_url'] . $this->saveUrl;
        $curl = new BackendDecorator(new CurlTransport(), new Config());
        $curl->write(CurlInterface::POST, $url, '1.1', [], $data);
        $response = $curl->read();
        $curl->close();
        if (!strpos($response, 'data-ui-id="messages-message-success"')) {
            throw new \Exception("Store View entity creating  by curl handler was not successful! Response: $response");
        }

        return ['store_id' => $this->getStoreId($fixture->getName())];
    }

    /**
     * Prepare data from text to values
     *
     * @param FixtureInterface $fixture
     * @return array
     */
    protected function prepareData(FixtureInterface $fixture)
    {
        $data = [
            'store' => $this->replaceMappingData($fixture->getData()),
            'store_action' => 'add',
            'store_type' => 'store',
        ];
        $data['store']['group_id'] = $fixture->getDataFieldConfig('group_id')['source']->getStoreGroup()->getGroupId();
        $data['store']['store_id'] = isset($data['store']['store_id']) ? $data['store']['store_id'] : '';

        return $data;
    }

    /**
     * Get Store id by name after creating Store
     *
     * @param string $name
     * @return int|null
     * @throws \Exception
     */
    protected function getStoreId($name)
    {
        //Set pager limit to 2000 in order to find created store view by name
        $url = $_ENV['app_backend_url'] . 'admin/system_store/index/sort/store_title/dir/asc/limit/2000';
        $curl = new BackendDecorator(new CurlTransport(), new Config);
        $curl->addOption(CURLOPT_HEADER, 1);
        $curl->write(CurlInterface::POST, $url, '1.0');
        $response = $curl->read();

        $expectedUrl = '/admin/system_store/editStore/store_id/';
        $expectedUrl = preg_quote($expectedUrl);
        $expectedUrl = str_replace('/', '\/', $expectedUrl);
        preg_match('/' . $expectedUrl . '([0-9]*)\/(.)*>' . $name . '<\/a>/', $response, $matches);

        if (empty($matches)) {
            throw new \Exception('Cannot find store id');
        }

        return empty($matches[1]) ? null : $matches[1];
    }

    /**
     * Encoded filter parameters
     *
     * @param array $filter
     * @return string
     */
    protected function encodeFilter(array $filter)
    {
        $result = [];
        foreach ($filter as $name => $value) {
            $result[] = "{$name}={$value}";
        }
        $result = implode('&', $result);

        return base64_encode($result);
    }
}
