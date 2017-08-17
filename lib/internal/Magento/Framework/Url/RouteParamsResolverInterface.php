<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Url;

/**
 * Route parameters resolver.
 *
 * @api
 */
interface RouteParamsResolverInterface
{
    /**
     * Set route params
     *
     * @param array $data
     * @param boolean $unsetOldParams
     * @return RouteParamsResolverInterface
     */
    public function setRouteParams(array $data, $unsetOldParams = true);

    /**
     * Set route param
     *
     * @param string $key
     * @param mixed $data
     * @return RouteParamsResolverInterface
     */
    public function setRouteParam($key, $data);

    /**
     * Retrieve route params
     *
     * @return array
     */
    public function getRouteParams();

    /**
     * Retrieve route param
     *
     * @param string $key
     * @return mixed
     */
    public function getRouteParam($key);
}
