<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\Handler\StoreGroup;

use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Handler\Curl as AbstractCurl;
use Magento\Mtf\Util\Protocol\CurlInterface;
use Magento\Mtf\Util\Protocol\CurlTransport;
use Magento\Mtf\Util\Protocol\CurlTransport\BackendDecorator;

/**
 * Class Curl
 * Curl handler for creating Store Group
 */
class Curl extends AbstractCurl implements StoreGroupInterface
{
    /**
     * POST request for creating store group
     *
     * @param FixtureInterface $fixture
     * @return array
     * @throws \Exception
     */
    public function persist(FixtureInterface $fixture = null)
    {
        $data = $this->prepareData($fixture);
        $url = $_ENV['app_backend_url'] . 'admin/system_store/save';
        $curl = new BackendDecorator(new CurlTransport(), $this->_configuration);
        $curl->write($url, $data);
        $response = $curl->read();
        $curl->close();
        if (!strpos($response, 'data-ui-id="messages-message-success"')) {
            throw new \Exception("Store group entity creating by curl handler was not successful! Response: $response");
        }

        return ['group_id' => $this->getStoreGroupIdByGroupName($fixture->getName())];
    }

    /**
     * Get store id by store name
     *
     * @param string $storeName
     * @return int
     * @throws \Exception
     */
    protected function getStoreGroupIdByGroupName($storeName)
    {
        $url = $_ENV['app_backend_url'] . 'mui/index/render/';
        $data = [
            'namespace' => 'store_listing',
            'filters' => [
                'placeholder' => true,
                'group_title' => $storeName,
            ],
            'paging' => [
                'pageSize' => 1,
            ]
        ];
        $curl = new BackendDecorator(new CurlTransport(), $this->_configuration);

        $curl->write($url, $data, CurlInterface::POST);
        $response = $curl->read();
        $curl->close();

        preg_match('/store_listing_data_source.+items.+"group_id":"(\d+)"/', $response, $match);

        if (empty($match)) {
            throw new \Exception('Cannot find store group id');
        }

        return (int)$match[1];
    }

    /**
     * Prepare data from text to values
     *
     * @param FixtureInterface $fixture
     * @return array
     */
    protected function prepareData(FixtureInterface $fixture)
    {
        $categoryId = $fixture->getDataFieldConfig('root_category_id')['source']->getCategory()->getId();
        $websiteId = $fixture->getDataFieldConfig('website_id')['source']->getWebsite()->getWebsiteId();
        $data = [
            'group' => [
                'name' => $fixture->getName(),
                'root_category_id' => $categoryId,
                'website_id' => $websiteId,
                'group_id' => $fixture->hasData('group_id') ? $fixture->getGroupId() : '',
                'code' => $fixture->hasData('code') ? $fixture->getCode() : '',
            ],
            'store_action' => 'add',
            'store_type' => 'group',
        ];

        return $data;
    }
}
