<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Util\Protocol\CurlTransport;

use Magento\Integration\Test\Fixture\Integration;
use Magento\Mtf\Config\DataInterface;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\ObjectManager;
use Magento\Mtf\Util\Protocol\CurlInterface;
use Magento\Mtf\Util\Protocol\CurlTransport;

/**
 * Curl transport on webapi.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class WebapiDecorator implements CurlInterface
{
    /**
     * Xpath to token in configuration file.
     */
    const CONFIG_TOKEN_PATH = 'handler/0/webapi/0/token/0/value';

    /**
     * Object manager.
     *
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Curl transport protocol.
     *
     * @var CurlTransport
     */
    protected $transport;

    /**
     * System config.
     *
     * @var DataInterface
     */
    protected $configuration;

    /**
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Api headers.
     *
     * @var array
     */
    protected $headers = [
        'Accept: application/json',
        'Content-Type:application/json',
    ];

    /**
     * Response data.
     *
     * @var string
     */
    protected $response;

    /**
     * @construct
     * @param ObjectManager $objectManager
     * @param CurlTransport $transport
     * @param DataInterface $configuration
     * @param FixtureFactory $fixtureFactory
     */
    public function __construct(
        ObjectManager $objectManager,
        CurlTransport $transport,
        DataInterface $configuration,
        FixtureFactory $fixtureFactory
    ) {
        $this->objectManager = $objectManager;
        $this->transport = $transport;
        $this->configuration = $configuration;
        $this->fixtureFactory = $fixtureFactory;

        $this->init();
    }

    /**
     * Init integration account.
     *
     * @return void
     */
    protected function init()
    {
        $integrationToken = $this->configuration->get(self::CONFIG_TOKEN_PATH);

        if (null === $integrationToken || !$this->isValidIntegration()) {
            $this->disableSecretKey();
            /** @var \Magento\Integration\Test\Fixture\Integration $integration */
            $integration = $this->fixtureFactory->create(
                \Magento\Integration\Test\Fixture\Integration::class,
                ['dataset' => 'default_active']
            );
            $integration->persist();

            $this->setConfiguration($integration);
        }
    }

    /**
     * Disable secret key before creating and activating integration.
     *
     * @return void
     */
    protected function disableSecretKey()
    {
        $config = $this->fixtureFactory->create(
            \Magento\Config\Test\Fixture\ConfigData::class,
            ['dataset' => 'secret_key_disable']
        );
        $config->persist();
    }

    /**
     * Set integration data to configuration file.
     *
     * @param Integration $integration
     * @return void
     */
    protected function setConfiguration(Integration $integration)
    {
        $fileConfig = MTF_BP . '/etc/config.xml';
        $dom = new \DOMDocument();
        if (!file_exists($fileConfig)) {
            copy(MTF_BP . '/etc/config.xml.dist', $fileConfig);
        }
        $dom->load($fileConfig);

        $webapiToken = (new \DOMXPath($dom))->query('//config/handler/webapi/token')->item(0);
        if ($webapiToken) {
            $webapiToken->nodeValue = $integration->getToken();
        } else {
            $webapi = (new \DOMXPath($dom))->query('//config/handler/webapi')->item(0);
            $webapi->appendChild($dom->createElement('token', $integration->getToken()));
        }

        $dom->save($fileConfig);
        $this->configuration = $this->objectManager->create(\Magento\Mtf\Config\DataInterface::class);
    }

    /**
     * Check ability access to webapi.
     *
     * @return bool
     */
    protected function isValidIntegration()
    {
        $this->write($_ENV['app_frontend_url'] . 'rest/V1/modules', [], CurlInterface::GET);
        $response = json_decode($this->read(), true);

        return (null !== $response) && !isset($response['message']);
    }

    /**
     * Send request to the remote server.
     *
     * @param string $url
     * @param array $params
     * @param string $method
     * @param array $headers
     * @return void
     */
    public function write($url, $params = [], $method = CurlInterface::POST, $headers = [])
    {
        $headers = array_merge(
            ['Authorization: Bearer ' . $this->configuration->get(self::CONFIG_TOKEN_PATH)],
            $this->headers,
            $headers
        );

        $this->transport->write($url, json_encode($params), $method, $headers);
    }

    /**
     * Read response from server.
     *
     * @return string
     */
    public function read()
    {
        $this->response = $this->transport->read();
        return $this->response;
    }

    /**
     * Add additional option to cURL.
     *
     * @param  int $option the CURLOPT_* constants
     * @param  mixed $value
     * @return void
     */
    public function addOption($option, $value)
    {
        $this->transport->addOption($option, $value);
    }

    /**
     * Close the connection to the server.
     *
     * @return void
     */
    public function close()
    {
        $this->transport->close();
    }
}
