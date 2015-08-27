<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Test\Handler\Integration;

use Magento\Integration\Test\Fixture\Integration;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Handler\Curl as AbstractCurl;
use Magento\Mtf\Util\Protocol\CurlInterface;
use Magento\Mtf\Util\Protocol\CurlTransport;
use Magento\Mtf\Util\Protocol\CurlTransport\BackendDecorator;

/**
 * Curl handler for creating Integration.
 */
class Curl extends AbstractCurl implements IntegrationInterface
{
    /**
     * Pattern for to get field value.
     *
     * @var string
     */
    protected $patternField = '/<input[^>]+name="%s"[^>]+value="([^"]+)"[^>]+\/>/';

    /**
     * Mapping fields.
     *
     * @var array
     */
    protected $fields = [
        'consumer_key',
        'consumer_secret',
        'token',
        'token_secret',
    ];

    /**
     * Create integration using cURL client.
     *
     * @param FixtureInterface $fixture
     * @throws \Exception
     * @return array
     */
    public function persist(FixtureInterface $fixture = null)
    {
        /** @var Integration $fixture */
        /** Prepare data for the post to integration save URL */
        $data = $fixture->getData();
        $data['all_resources'] = ($data['resource_access'] === 'All') ? 1 : 0;
        $curl = new BackendDecorator(new CurlTransport(), $this->_configuration);
        $result = [];

        /** Create new integration via cURL */
        $url = $_ENV['app_backend_url'] . 'admin/integration/save';
        $curl->write($url, $data);
        $response = $curl->read();
        if (!strpos($response, 'data-ui-id="messages-message-success"')) {
            $curl->close();
            throw new \Exception("Integration creation by curl handler was not successful! Response: $response");
        }

        /* Get integration id */
        $filter = $this->encodeFilter(['name' => $fixture->getName()]);
        $url = $_ENV['app_backend_url'] . 'admin/integration/grid/page/2/filter/' . $filter;
        $curl->write($url, $data);
        $response = $curl->read();
        preg_match('~<td.*?>.*?' . $data['name'] . '.*?/integration/edit/id/(\d+)/~siu', $response, $matches);
        $result['integration_id'] = isset($matches[1]) ? $matches[1] : null;

        /** Activate integration */
        if (isset($data['status']) && 'Active' == $data['status']) {
            $url = 'admin/integration/permissionsDialog/id/' . $result['integration_id'] . '/reauthorize/0/';
            $curl->write($_ENV['app_backend_url'] . $url, [], CurlInterface::GET);
            $curl->read();

            $url = 'admin/integration/tokensDialog/id/' . $result['integration_id'] . '/reauthorize/0/';
            $curl->write($_ENV['app_backend_url'] . $url, [], CurlInterface::GET);
            $response = $curl->read();
            foreach ($this->fields as $field) {
                $pattern = sprintf($this->patternField, $field);
                preg_match($pattern, $response, $matches);
                $result[$field] = isset($matches[1]) ? $matches[1] : null;
            }
        }

        $curl->close();
        return $result;
    }
}
