<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Test\Handler\CmsBlock;

use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Handler\Curl as AbstractCurl;
use Magento\Mtf\Util\Protocol\CurlTransport;
use Magento\Mtf\Util\Protocol\CurlTransport\BackendDecorator;

/**
 * Curl handler for creating CMS Block.
 */
class Curl extends AbstractCurl implements CmsBlockInterface
{
    /**
     * Url for saving data.
     *
     * @var string
     */
    protected $saveUrl = 'cms/block/save/back/edit';

    /**
     * Mapping values for data.
     *
     * @var array
     */
    protected $mappingData = [
        'is_active' => [
            'Yes' => 1,
            'No' => 0,
        ],
    ];

    /**
     * Mapping values for Stores.
     *
     * @var array
     */
    protected $stores = [
        'All Store Views' => 0,
    ];

    /**
     * POST request for creating CMS Block.
     *
     * @param FixtureInterface|null $fixture [optional]
     * @return array
     * @throws \Exception
     */
    public function persist(FixtureInterface $fixture = null)
    {
        $data = $this->prepareData($fixture);
        $url = $_ENV['app_backend_url'] . $this->saveUrl;
        $curl = new BackendDecorator(new CurlTransport(), $this->_configuration);
        $curl->write($url, $data);
        $response = $curl->read();
        $curl->close();
        if (strpos($response, 'data-ui-id="messages-message-success"') === false) {
            throw new \Exception("CMS Block entity creating by curl handler was not successful! Response: $response");
        }

        preg_match("`block_id\/(\d*?)\/`", $response, $matches);
        $id = isset($matches[1]) ? $matches[1] : null;

        return ['block_id' => $id];
    }

    /**
     * Prepare data from text to values.
     *
     * @param FixtureInterface $fixture
     * @return array
     */
    protected function prepareData($fixture)
    {
        $data = $this->replaceMappingData($fixture->getData());
        if (isset($data['stores'])) {
            $stores = [];
            foreach ($data['stores'] as $store) {
                $stores[] = isset($this->stores[$store]) ? $this->stores[$store] : $store;
            }
            $data['stores'] = $stores;
        }

        return $data;
    }
}
