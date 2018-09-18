<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Util\Protocol\CurlTransport;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Mtf\Util\Protocol\CurlInterface;
use Magento\Mtf\Util\Protocol\CurlTransport;

/**
 * Curl transport on frontend.
 */
class FrontendDecorator implements CurlInterface
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
     * Cookies data.
     *
     * @var string
     */
    protected $cookies = '';

    /**
     * @constructor
     * @param CurlTransport $transport
     * @param Customer $customer
     */
    public function __construct(CurlTransport $transport, Customer $customer)
    {
        $this->transport = $transport;
        $this->authorize($customer);
    }

    /**
     * Authorize customer on frontend.
     *
     * @param Customer $customer
     * @throws \Exception
     * @return void
     */
    protected function authorize(Customer $customer)
    {
        $url = $_ENV['app_frontend_url'] . 'customer/account/login/';
        $this->transport->write($url, [], CurlInterface::GET);
        $this->read();
        $url = $_ENV['app_frontend_url'] . 'customer/account/loginPost/';
        $data = [
            'login[username]' => $customer->getEmail(),
            'login[password]' => $customer->getPassword(),
            'form_key' => $this->formKey,
        ];
        $this->transport->write($url, $data, CurlInterface::POST, ['Set-Cookie:' . $this->cookies]);
        $response = $this->read();
        if (strpos($response, 'customer/account/login') !== false) {
            throw new \Exception($customer->getFirstname() . ', cannot be logged in by curl handler!');
        }
    }

    /**
     * Init Form Key from response.
     *
     * @return void
     */
    protected function initFormKey()
    {
        $str = substr($this->response, strpos($this->response, 'form_key'));
        preg_match('/value="(.*)" \/>/', $str, $matches);
        if (!empty($matches[1])) {
            $this->formKey = $matches[1];
        }
    }

    /**
     * Init Cookies from response.
     *
     * @return void
     */
    protected function initCookies()
    {
        preg_match_all('|Set-Cookie: (.*);|U', $this->response, $matches);
        if (!empty($matches[1])) {
            $this->cookies = implode('; ', $matches[1]);
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
     */
    public function write($url, $params = [], $method = CurlInterface::POST, $headers = [])
    {
        if ($this->formKey) {
            $params['form_key'] = $this->formKey;
        }
        $headers = ['Set-Cookie:' . $this->cookies];
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
        $this->initCookies();
        $this->initFormKey();
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
