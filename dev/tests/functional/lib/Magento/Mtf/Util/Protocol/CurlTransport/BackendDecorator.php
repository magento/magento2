<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Util\Protocol\CurlTransport;

use Magento\Mtf\Config\DataInterface;
use Magento\Mtf\Util\Protocol\CurlInterface;
use Magento\Mtf\Util\Protocol\CurlTransport;

/**
 * Curl transport on backend.
 */
class BackendDecorator implements CurlInterface
{
    /**
     * Curl transport protocol.
     *
     * @var CurlTransport
     */
    protected $transport;

    /**
     * Form key.
     *
     * @var string
     */
    protected $formKey = null;

    /**
     * Response data.
     *
     * @var string
     */
    protected $response;

    /**
     * System config.
     *
     * @var DataInterface
     */
    protected $configuration;

    /**
     * @constructor
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
     * Authorize customer on backend.
     *
     * @throws \Exception
     * @return void
     */
    protected function authorize()
    {
        // There are situations where magento application backend url could be slightly different from the environment
        // variable we know. It could be intentionally (e.g. InstallTest) or unintentionally. We would still want tests
        // to run in this case.
        // When the original app_backend_url does not work, we will try 4 variants of the it. i.e. with and without
        // url rewrite, http and https.
        $urls = [];
        $originalUrl = rtrim($_ENV['app_backend_url'], '/') . '/';
        $urls[] = $originalUrl;
        // It could be the case that the page needs a refresh, so we will try the original one twice
        $urls[] = $originalUrl;
        if (strpos($originalUrl, '/index.php') !== false) {
            $url2 = str_replace('/index.php', '', $originalUrl);
        } else {
            $url2 = $originalUrl . 'index.php/';
        }
        $urls[] = $url2;
        if (strpos($originalUrl, 'https') !== false) {
            $urls[] = str_replace('https', 'http', $originalUrl);
            $urls[] = str_replace('https', 'http', $url2);
        } else {
            $urls[] = str_replace('http', 'https', $originalUrl);
            $urls[] = str_replace('http', 'https', $url2);
        }

        $isAuthorized = false;
        foreach ($urls as $url) {
            try {
                // Perform GET to backend url so form_key is set
                $this->transport->write($url, [], CurlInterface::GET);
                $this->read();

                $authUrl = $url . $this->configuration->get('application/0/backendLoginUrl/0/value');
                $data = [
                    'login[username]' => $this->configuration->get('application/0/backendLogin/0/value'),
                    'login[password]' => $this->configuration->get('application/0/backendPassword/0/value'),
                    'form_key' => $this->formKey,
                ];

                $this->transport->write($authUrl, $data, CurlInterface::POST);
                $response = $this->read();
                if (strpos($response, 'login-form') !== false) {
                    continue;
                }
                $isAuthorized = true;
                $_ENV['app_backend_url'] = $url;
                break;
            } catch (\Exception $e) {
                continue;
            }
        }
        if ($isAuthorized == false) {
            throw new \Exception('Admin user cannot be logged in by curl handler!');
        }
    }

    /**
     * Init Form Key from response.
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
     * Send request to the remote server.
     *
     * @param string $url
     * @param mixed $params
     * @param string $method
     * @param mixed $headers
     * @return void
     * @throws \Exception
     */
    public function write($url, $params = [], $method = CurlInterface::POST, $headers = [])
    {
        if ($this->formKey) {
            $params['form_key'] = $this->formKey;
        } else {
            throw new \Exception(sprintf('Form key is absent! Url: "%s" Response: "%s"', $url, $this->response));
        }
        $this->transport->write($url, http_build_query($params), $method, $headers);
    }

    /**
     * Read response from server.
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
     * Add additional option to cURL.
     *
     * @param int $option the CURLOPT_* constants
     * @param mixed $value
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
