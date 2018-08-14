<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * List fields of activation data for getting from page.
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
        $curl->close();
        if (!strpos($response, 'data-ui-id="messages-message-success"')) {
            throw new \Exception("Integration creation by curl handler was not successful! Response: $response");
        }
        $result['integration_id'] = $this->getIntegrationId($fixture);

        if (isset($data['status']) && 'Active' == $data['status']) {
            $fields = $this->activateIntegration($result['integration_id']);
            $result = array_merge($result, $fields);
        }

        return $result;
    }

    /**
     * Get integration id.
     *
     * @param Integration $integration
     * @return int|null
     */
    protected function getIntegrationId(Integration $integration)
    {
        $name = $integration->getName();
        $filter = base64_encode('name=' . $integration->getName());
        $url = $_ENV['app_backend_url'] . 'admin/integration/grid/filter/' . $filter;
        $curl = new BackendDecorator(new CurlTransport(), $this->_configuration);

        $curl->write($url);
        $response = $curl->read();
        $curl->close();

        preg_match('~<td.*?>.*?' . $name . '.*?/integration/edit/id/(\d+)/~siu', $response, $matches);
        return isset($matches[1]) ? $matches[1] : null;
    }

    /**
     * Activate integration.
     *
     * @param int $integrationId
     * @return array
     */
    protected function activateIntegration($integrationId)
    {
        $curl = new BackendDecorator(new CurlTransport(), $this->_configuration);
        $result = [];

        $url = 'admin/integration/permissionsDialog/id/' . $integrationId . '/reauthorize/0/';
        $curl->write($_ENV['app_backend_url'] . $url, [], CurlInterface::GET);
        $curl->read();

        $url = 'admin/integration/tokensDialog/id/' . $integrationId . '/reauthorize/0/';
        $curl->write($_ENV['app_backend_url'] . $url, [], CurlInterface::GET);
        $response = $curl->read();
        $curl->close();

        foreach ($this->fields as $field) {
            $pattern = sprintf($this->patternField, $field);
            preg_match($pattern, $response, $matches);
            $result[$field] = isset($matches[1]) ? $matches[1] : null;
        }

        return $result;
    }
}
