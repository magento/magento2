<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\UrlRewrite\Plugin\Controller;

use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\UrlRewrite\Controller\Router as Subject;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Router\PathConfigInterface;
use Magento\UrlRewrite\Provider\RequestPathProviderInterface;

/**
 * Class RouterPlugin
 * @package Magento\UrlRewrite\Plugin\Controller
 */
class RouterPlugin
{
    /** @var PathConfigInterface */
    private $pathConfig;

    /** @var  RequestPathProviderInterface */
    private $provider;

    /**
     * RouterPlugin constructor.
     * @param PathConfigInterface           $pathConfig
     * @param RequestPathProviderInterface  $requestPathProvider
     */
    public function __construct(
        PathConfigInterface $pathConfig,
        RequestPathProviderInterface $requestPathProvider
    ) {
        $this->pathConfig = $pathConfig;
        $this->provider   = $requestPathProvider;
    }

    /**
     * @param Subject           $subject
     * @param RequestInterface  $request
     *
     * @return array
     */
    public function beforeMatch(Subject $subject, RequestInterface $request)
    {
        $path = $this->getNormalizedPathInfo($request);

        if (empty($path)) {
            $requestPath = $this->pathConfig->getDefaultPath();

            /** check that request path is not target path */
            $requestPath = $this->provider->getRequestPath($requestPath);

            $request->setPathInfo($requestPath);
        }

        return [
            $request
        ];
    }

    /**
     * Get normalized request path
     *
     * @param RequestInterface|HttpRequest $request
     * @return string
     */
    private function getNormalizedPathInfo(RequestInterface $request): string
    {
        $path = $request->getPathInfo();
        /**
         * If request contains query params then we need to trim a slash in end of the path.
         * For example:
         * the original request is: http://my-host.com/category-url-key.html/?color=black
         * where the original path is: category-url-key.html/
         * and the result path will be: category-url-key.html
         *
         * It need to except a redirect like this:
         * http://my-host.com/category-url-key.html/?color=black => http://my-host.com/category-url-key.html
         */
        if (!empty($path) && $request->getQuery()->count()) {
            $path = rtrim($path, '/');
        }

        return (string)$path;
    }
}
