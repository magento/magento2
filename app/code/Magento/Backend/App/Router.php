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
 */
class Router extends \Magento\Framework\App\Router\Base
{
    /**
     * @var \Magento\Framework\UrlInterface $url
     */
    protected $_url;

    /**
     * List of required request parameters
     * Order sensitive
     *
     * @var string[]
     */
    protected $_requiredParams = ['areaFrontName', 'moduleFrontName', 'actionPath', 'actionName'];

    /**
     * We need to have noroute action in this router
     * not to pass dispatching to next routers
     *
     * @var bool
     */
    protected $applyNoRoute = true;

    /**
     * @var string
     */
    protected $pathPrefix = \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE;

    /**
     * Check whether redirect should be used for secure routes
     *
     * @return bool
     */
    protected function _shouldRedirectToSecure()
    {
        return false;
    }
}
