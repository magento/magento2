<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
    protected $saveUrl = 'search/synonyms/save/';

    /**
     * Mapping values for data.
     *
     * @var array
     */
    protected $mappingData = [
        'mergeOnConflict' => [
            'Yes' => 1,
            'No' => 0,
        ],
        'scope_id' => [
            'All Websites' => '0:0',
            'All Store Views' => '1:0',
            'Default Store View' => '1:1',
        ],
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
        $data = $this->replaceMappingData($fixture->getData());

        $url = $_ENV['app_backend_url'] . $this->saveUrl;
        $curl = new BackendDecorator(new CurlTransport(), $this->_configuration);
        $curl->write($url, $data);
        $response = $curl->read();
        $curl->close();
        if (!strpos($response, 'data-ui-id="messages-message-success"')) {
            throw new \Exception(
                "Synonym Group entity creation by curl handler was not successful! Response: $response"
            );
        }

        preg_match("`group_id\/(\d*?)\/`", $response, $matches);
        $id = isset($matches[1]) ? $matches[1] : null;

        return ['group_id' => $id];
    }
}
