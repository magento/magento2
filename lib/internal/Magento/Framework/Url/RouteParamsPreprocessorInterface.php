<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Url;

/**
 * Route parameters preprocessor interface.
 *
 * @api
 */
interface RouteParamsPreprocessorInterface
{
    /**
     * Processes route params.
     *
     * @param string $areaCode
     * @param string|null $routePath
     * @param array|null $routeParams
     * @return array|null
     */
    public function execute($areaCode, $routePath, $routeParams);
}
