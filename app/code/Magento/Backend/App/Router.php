<?php
/**
 * Backend router
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 */
namespace Magento\Backend\App;

/**
 * @api
 * @since 2.0.0
 */
class Router extends \Magento\Framework\App\Router\Base
{
    /**
     * @var \Magento\Framework\UrlInterface $url
     * @since 2.0.0
     */
    protected $_url;

    /**
     * List of required request parameters
     * Order sensitive
     *
     * @var string[]
     * @since 2.0.0
     */
    protected $_requiredParams = ['areaFrontName', 'moduleFrontName', 'actionPath', 'actionName'];

    /**
     * We need to have noroute action in this router
     * not to pass dispatching to next routers
     *
     * @var bool
     * @since 2.0.0
     */
    protected $applyNoRoute = true;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $pathPrefix = \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE;

    /**
     * Check whether redirect should be used for secure routes
     *
     * @return bool
     * @since 2.0.0
     */
    protected function _shouldRedirectToSecure()
    {
        return false;
    }
}
