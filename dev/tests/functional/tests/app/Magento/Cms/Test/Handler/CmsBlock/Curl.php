<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Test\Handler\CmsBlock;

use Mtf\Fixture\FixtureInterface;
use Mtf\Handler\Curl as AbstractCurl;
use Mtf\System\Config;
use Mtf\Util\Protocol\CurlInterface;
use Mtf\Util\Protocol\CurlTransport;
use Mtf\Util\Protocol\CurlTransport\BackendDecorator;

/**
 * Class Curl
 * Curl handler for creating CMS Block
 */
class Curl extends AbstractCurl implements CmsBlockInterface
{
    /**
     * Url for saving data
     *
     * @var string
     */
    protected $saveUrl = 'cms/block/save/back/edit';

    /**
     * Mapping values for data
     *
     * @var array
     */
    protected $mappingData = [
        'is_active' => [
            'Enabled' => 1,
            'Disabled' => 0,
        ],
    ];

    /**
     * Mapping values for Stores
     *
     * @var array
     */
    protected $stores = [
        'All Store Views' => 0,
    ];

    /**
     * POST request for creating CMS Block
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
        $curl->write(CurlInterface::POST, $url, '1.0', [], $data);
        $response = $curl->read();
        $curl->close();
        if (!strpos($response, 'data-ui-id="messages-message-success"')) {
            throw new \Exception("CMS Block entity creating by curl handler was not successful! Response: $response");
        }

        preg_match("`block_id\/(\d*?)\/`", $response, $matches);
        $id = isset($matches[1]) ? $matches[1] : null;

        return ['block_id' => $id];
    }

    /**
     * Prepare data from text to values
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
