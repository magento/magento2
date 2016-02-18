<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Test\Handler\SynonymGroup;

use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Handler\Curl as AbstractCurl;
use Magento\Mtf\Util\Protocol\CurlTransport;
use Magento\Mtf\Util\Protocol\CurlTransport\BackendDecorator;

/**
 * Curl handler for creating Synonym Group.
 */
class Curl extends AbstractCurl implements SynonymGroupInterface
{
    /**
     * Url for saving data.
     *
     * @var string
     */
    protected $saveUrl = 'search/synonym/save/back/edit';

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
    protected $scope_id = [
        'All Store Views' => 0,
    ];

    /**
     * POST request for creating Synonym Group.
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
        if (!strpos($response, 'data-ui-id="messages-message-success"')) {
            throw new \Exception("Synonym Group entity creating by curl handler was not successful! Response: $response");
        }

        preg_match("`group_id\/(\d*?)\/`", $response, $matches);
        $id = isset($matches[1]) ? $matches[1] : null;

        return ['group_id' => $id];
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
        if (isset($data['scope_id'])) {
            $stores = [];
            foreach ($data['scope_id'] as $store) {
                $stores[] = isset($this->scope_id[$store]) ? $this->scope_id[$store] : $store;
            }
            $data['scope_id'] = $stores;
        }

        return $data;
    }
}
