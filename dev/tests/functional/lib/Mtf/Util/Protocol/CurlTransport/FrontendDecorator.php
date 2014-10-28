<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Mtf\Util\Protocol\CurlTransport;

use Mtf\Util\Protocol\CurlTransport;
use Mtf\Util\Protocol\CurlInterface;
use Magento\Customer\Test\Fixture\CustomerInjectable;

/**
 * Class FrontendDecorator
 * Curl transport on frontend
 */
class FrontendDecorator implements CurlInterface
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
     * Cookies data
     *
     * @var string
     */
    protected $cookies = '';

    /**
     * Constructor
     *
     * @param CurlTransport $transport
     * @param CustomerInjectable $customer
     */
    public function __construct(CurlTransport $transport, CustomerInjectable $customer)
    {
        $this->transport = $transport;
        $this->authorize($customer);
    }

    /**
     * Authorize customer on frontend
     *
     * @param CustomerInjectable $customer
     * @throws \Exception
     * @return void
     */
    protected function authorize(CustomerInjectable $customer)
    {
        $url = $_ENV['app_frontend_url'] . 'customer/account/login/';
        $this->transport->write(CurlInterface::POST, $url);
        $this->read();
        $url = $_ENV['app_frontend_url'] . 'customer/account/loginPost/';
        $data = [
            'login[username]' => $customer->getEmail(),
            'login[password]' => $customer->getPassword(),
            'form_key' => $this->formKey
        ];
        $this->transport->write(CurlInterface::POST, $url, '1.0', ['Set-Cookie:' . $this->cookies], $data);
        $response = $this->read();
        if (strpos($response, 'customer/account/login')) {
            throw new \Exception($customer->getFirstname() . ', cannot be logged in by curl handler!');
        }
    }

    /**
     * Init Form Key from response
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
     * Init Cookies from response
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
     * Send request to the remote server
     *
     * @param string $method
     * @param string $url
     * @param string $httpVer
     * @param array $headers
     * @param array $params
     * @return void
     */
    public function write($method, $url, $httpVer = '1.1', $headers = [], $params = [])
    {
        $this->transport->write($method, $url, $httpVer, ['Set-Cookie:' . $this->cookies], http_build_query($params));
    }

    /**
     * Read response from server
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
