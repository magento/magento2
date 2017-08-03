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
 * @since 2.0.0
 */
interface RouteParamsResolverInterface
{
    /**
     * Set route params
     *
     * @param array $data
     * @param boolean $unsetOldParams
     * @return RouteParamsResolverInterface
     * @since 2.0.0
     */
    public function setRouteParams(array $data, $unsetOldParams = true);

    /**
     * Set route param
     *
     * @param string $key
     * @param mixed $data
     * @return RouteParamsResolverInterface
     * @since 2.0.0
     */
    public function setRouteParam($key, $data);

    /**
     * Retrieve route params
     *
     * @return array
     * @since 2.0.0
     */
    public function getRouteParams();

    /**
     * Retrieve route param
     *
     * @param string $key
     * @return mixed
     * @since 2.0.0
     */
    public function getRouteParam($key);
}
