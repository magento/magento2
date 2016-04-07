<?php
/**
 * Response redirect interface
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Response;

interface RedirectInterface
{
    const PARAM_NAME_REFERER_URL = 'referer_url';

    const PARAM_NAME_ERROR_URL = 'error_url';

    const PARAM_NAME_SUCCESS_URL = 'success_url';

    /**
     * Identify referer url via all accepted methods (HTTP_REFERER, regular or base64-encoded request param)
     *
     * @return string
     */
    public function getRefererUrl();

    /**
     * Set referer url for redirect in response
     *
     * @param   string $defaultUrl
     * @return  string
     */
    public function getRedirectUrl($defaultUrl = null);

    /**
     * Redirect to error page
     *
     * @param string $defaultUrl
     * @return  string
     */
    public function error($defaultUrl);

    /**
     * Redirect to success page
     *
     * @param string $defaultUrl
     * @return string
     */
    public function success($defaultUrl);

    /**
     * Update path params for url builder
     *
     * @param array $arguments
     * @return array
     */
    public function updatePathParams(array $arguments);

    /**
     * Set redirect into response
     *
     * @param \Magento\Framework\App\ResponseInterface $response
     * @param string $path
     * @param array $arguments
     * @return void
     */
    public function redirect(\Magento\Framework\App\ResponseInterface $response, $path, $arguments = []);
}
