<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\HTTP;

/**
 * Library for working with HTTP authentication
 * @since 2.0.0
 */
class Authentication
{
    /**
     * Request object
     *
     * @var \Magento\Framework\App\RequestInterface
     * @since 2.0.0
     */
    protected $request;

    /**
     * Response object
     *
     * @var \Magento\Framework\App\ResponseInterface
     * @since 2.0.0
     */
    protected $response;

    /**
     * @param \Magento\Framework\App\RequestInterface $httpRequest
     * @param \Magento\Framework\App\ResponseInterface $httpResponse
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getCredentials()
    {
        $server = $this->request->getServerValue();
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

        return [$user, $pass];
    }

    /**
     * Set "auth failed" headers to the specified response object
     *
     * @param string $realm
     * @return void
     * @since 2.0.0
     */
    public function setAuthenticationFailed($realm)
    {
        $this->response->setStatusHeader(401, '1.1', 'Unauthorized');
        $this->response->setHeader(
            'WWW-Authenticate',
            'Basic realm="' . $realm . '"'
        )->setBody(
            '<h1>401 Unauthorized</h1>'
        );
    }
}
