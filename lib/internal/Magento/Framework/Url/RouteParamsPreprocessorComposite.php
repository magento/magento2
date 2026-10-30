<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
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
