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
namespace Magento\Framework\HTTP;

/**
 * Library for working with HTTP authentication
 */
class Authentication
{
    /**
     * Request object
     *
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * Response object
     *
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $response;

    /**
     * @param \Magento\Framework\App\RequestInterface $httpRequest
     * @param \Magento\Framework\App\ResponseInterface $httpResponse
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $httpRequest,
        \Magento\Framework\App\ResponseInterface $httpResponse
    ) {
        $this->request = $httpRequest;
        $this->response = $httpResponse;
    }

    /**
     * Extract "login" and "password" credentials from HTTP-request
     *
     * Returns plain array with 2 items: login and password respectively
     *
     * @return array
     */
    public function getCredentials()
    {
        $server = $this->request->getServer();
        $user = '';
        $pass = '';

        if (empty($server['HTTP_AUTHORIZATION'])) {
            foreach ($server as $k => $v) {
                if (substr($k, -18) === 'HTTP_AUTHORIZATION' && !empty($v)) {
                    $server['HTTP_AUTHORIZATION'] = $v;
                    break;
                }
            }
        }

        if (isset($server['PHP_AUTH_USER']) && isset($server['PHP_AUTH_PW'])) {
            $user = $server['PHP_AUTH_USER'];
            $pass = $server['PHP_AUTH_PW'];
        } elseif (!empty($server['HTTP_AUTHORIZATION'])) {
            /**
             * IIS Note: for HTTP authentication to work with IIS,
             * the PHP directive cgi.rfc2616_headers must be set to 0 (the default value).
             */
            $auth = $server['HTTP_AUTHORIZATION'];
            list($user, $pass) = explode(':', base64_decode(substr($auth, strpos($auth, " ") + 1)));
        } elseif (!empty($server['Authorization'])) {
            $auth = $server['Authorization'];
            list($user, $pass) = explode(':', base64_decode(substr($auth, strpos($auth, " ") + 1)));
        }

        return array($user, $pass);
    }

    /**
     * Set "auth failed" headers to the specified response object
     *
     * @param string $realm
     * @return void
     */
    public function setAuthenticationFailed($realm)
    {
        $this->response->setHeader(
            'HTTP/1.1',
            '401 Unauthorized'
        )->setHeader(
            'WWW-Authenticate',
            'Basic realm="' . $realm . '"'
        )->setBody(
            '<h1>401 Unauthorized</h1>'
        );
    }
}
