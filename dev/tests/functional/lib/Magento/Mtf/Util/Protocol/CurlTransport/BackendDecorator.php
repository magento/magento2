<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Util\Protocol\CurlTransport;

use Magento\Mtf\Config\DataInterface;
use Magento\Mtf\Util\Protocol\CurlInterface;
use Magento\Mtf\Util\Protocol\CurlTransport;

/**
 * Class BackendDecorator
 * Curl transport on backend
 */
class BackendDecorator implements CurlInterface
{
    /**
     * Curl transport protocol
     *
     * @var CurlTransport
     */
    protected $transport;

    /**
     * Form key
     *
     * @var string
     */
    protected $formKey = null;

    /**
     * Response data
     *
     * @var string
     */
    protected $response;

    /**
     * System config
     *
     * @var DataInterface
     */
    protected $configuration;

    /**
     * Constructor
     *
     * @param CurlTransport $transport
     * @param DataInterface $configuration
     */
    public function __construct(CurlTransport $transport, DataInterface $configuration)
    {
        $this->transport = $transport;
        $this->configuration = $configuration;
        $this->authorize();
    }

    /**
     * Authorize customer on backend
     *
     * @throws \Exception
     * @return void
     */
    protected function authorize()
    {
        $url = $_ENV['app_backend_url'] . $this->configuration->get('application/0/backendLoginUrl/0/value');
        $data = [
            'login[username]' => $this->configuration->get('application/0/backendLogin/0/value'),
            'login[password]' => $this->configuration->get('application/0/backendPassword/0/value'),
        ];
        $this->transport->write(CurlInterface::POST, $url, '1.0', [], $data);
        $response = $this->read();
        if (strpos($response, 'page-login')) {
            throw new \Exception(
                'Admin user cannot be logged in by curl handler!'
            );
        }
    }

    /**
     * Init Form Key from response
     *
     * @return void
     */
    protected function initFormKey()
    {
        preg_match('!var FORM_KEY = \'(\w+)\';!', $this->response, $matches);
        if (!empty($matches[1])) {
            $this->formKey = $matches[1];
        }
    }

    /**
     * Send request to the remote server
     *
     * @param string $method
     * @param string $url
     * @param string $httpVer
     * @param array $headers
     * @param array $params
     * @return void
     * @throws \Exception
     */
    public function write($method, $url, $httpVer = '1.1', $headers = [], $params = [])
    {
        if ($this->formKey) {
            $params['form_key'] = $this->formKey;
        } else {
            throw new \Exception(sprintf('Form key is absent! Url: "%s" Response: "%s"', $url, $this->response));
        }
        $this->transport->write($method, $url, $httpVer, $headers, http_build_query($params));
    }

    /**
     * Read response from server
     *
     * @return string
     */
    public function read()
    {
        $this->response = $this->transport->read();
        $this->initFormKey();
        return $this->response;
    }

    /**
     * Add additional option to cURL
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
     * Close the connection to the server
     *
     * @return void
     */
    public function close()
    {
        $this->transport->close();
    }
}
