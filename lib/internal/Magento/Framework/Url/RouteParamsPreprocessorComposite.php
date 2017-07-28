<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Url;

/**
 * Route parameters composite preprocessor.
 * @since 2.1.0
 */
class RouteParamsPreprocessorComposite implements RouteParamsPreprocessorInterface
{
    /**
     * @var RouteParamsPreprocessorInterface[]
     * @since 2.1.0
     */
    private $routeParamsPreprocessors;

    /**
     * @param RouteParamsPreprocessorInterface[] $routeParamsPreprocessors
     * @since 2.1.0
     */
    public function __construct(array $routeParamsPreprocessors = [])
    {
        $this->routeParamsPreprocessors = $routeParamsPreprocessors;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function execute($areaCode, $routePath, $routeParams)
    {
        foreach ($this->routeParamsPreprocessors as $preprocessor) {
            $routeParams = $preprocessor->execute($areaCode, $routePath, $routeParams);
        }

        return $routeParams;
    }
}
