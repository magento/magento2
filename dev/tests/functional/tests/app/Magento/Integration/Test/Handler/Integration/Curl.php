<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Test\Handler\Integration;

use Mtf\Fixture\FixtureInterface;
use Mtf\Handler\Curl as AbstractCurl;
use Mtf\System\Config;
use Mtf\Util\Protocol\CurlInterface;
use Mtf\Util\Protocol\CurlTransport;
use Mtf\Util\Protocol\CurlTransport\BackendDecorator;

/**
 * Class Curl
 * Curl handler for creating Integration
 */
class Curl extends AbstractCurl implements IntegrationInterface
{
    /**
     * Create integration using cURL client.
     *
     * @param FixtureInterface $fixture
     * @throws \Exception
     * @return array
     */
    public function persist(FixtureInterface $fixture = null)
    {
        /** Prepare data for the post to integration save URL */
        $data = $fixture->getData();
        $data['all_resources'] = ($data['resource_access'] === 'All') ? 1 : 0;
        /** Initialize cURL client which is authenticated to the Magento backend */
        $curl = new BackendDecorator(new CurlTransport(), new Config());
        /** Create new integration via cURL */
        $url = $_ENV['app_backend_url'] . 'admin/integration/save';
        $curl->write(CurlInterface::POST, $url, '1.0', [], $data);
        $response = $curl->read();
        $curl->close();

        if (!strpos($response, 'data-ui-id="messages-message-success"')) {
            throw new \Exception("Integration creation by curl handler was not successful! Response: $response");
        }

        preg_match('~<td.*?>.*?' . $data['name'] . '.*?/integration/edit/id/(\d+)/~siu', $response, $matches);
        $id = isset($matches[1]) ? $matches[1] : null;

        return ['integration_id' => $id];
    }
}
