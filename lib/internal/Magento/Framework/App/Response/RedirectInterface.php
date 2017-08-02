<?php
/**
 * Response redirect interface
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Response;

/**
 * Interface \Magento\Framework\App\Response\RedirectInterface
 *
 * @since 2.0.0
 */
interface RedirectInterface
{
    const PARAM_NAME_REFERER_URL = 'referer_url';

    const PARAM_NAME_ERROR_URL = 'error_url';

    const PARAM_NAME_SUCCESS_URL = 'success_url';

    /**
     * Identify referer url via all accepted methods (HTTP_REFERER, regular or base64-encoded request param)
     *
     * @return string
     * @since 2.0.0
     */
    public function getRefererUrl();

    /**
     * Set referer url for redirect in response
     *
     * @param   string $defaultUrl
     * @return  string
     * @since 2.0.0
     */
    public function getRedirectUrl($defaultUrl = null);

    /**
     * Redirect to error page
     *
     * @param string $defaultUrl
     * @return  string
     * @since 2.0.0
     */
    public function error($defaultUrl);

    /**
     * Redirect to success page
     *
     * @param string $defaultUrl
     * @return string
     * @since 2.0.0
     */
    public function success($defaultUrl);

    /**
     * Update path params for url builder
     *
     * @param array $arguments
     * @return array
     * @since 2.0.0
     */
    public function updatePathParams(array $arguments);

    /**
     * Set redirect into response
     *
     * @param \Magento\Framework\App\ResponseInterface $response
     * @param string $path
     * @param array $arguments
     * @return void
     * @since 2.0.0
     */
    public function redirect(\Magento\Framework\App\ResponseInterface $response, $path, $arguments = []);
}
