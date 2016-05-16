<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Url;

/**
 * Route parameters composite preprocessor.
 */
class RouteParamsPreprocessorComposite implements RouteParamsPreprocessorInterface
{
    /**
     * @var RouteParamsPreprocessorInterface[]
     */
    private $routeParamsPreprocessors;

    /**
     * @param RouteParamsPreprocessorInterface[] $routeParamsPreprocessors
     */
    public function __construct(array $routeParamsPreprocessors = [])
    {
        $this->routeParamsPreprocessors = $routeParamsPreprocessors;
    }

    /**
     * {@inheritdoc}
     */
    public function execute($areaCode, $routePath, $routeParams)
    {
        foreach ($this->routeParamsPreprocessors as $preprocessor) {
            $routeParams = $preprocessor->execute($areaCode, $routePath, $routeParams);
        }

        return $routeParams;
    }
}
