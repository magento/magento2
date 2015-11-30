<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Design\Config;

use Magento\Framework\App\ScopeFallbackResolverInterface;
use Magento\Framework\App\Config as AppConfig;

class ValueChecker implements ValueCheckerInterface
{
    /**
     * @var ScopeFallbackResolverInterface
     */
    protected $fallbackResolver;

    /**
     * @var string
     */
    protected $value;

    /**
     * @var string
     */
    protected $scope;

    /**
     * @var string
     */
    protected $scopeId;

    /**
     * @var AppConfig
     */
    protected $appConfig;

    /**
     * @var string
     */
    protected $path;

    /**
     * @param ScopeFallbackResolverInterface $fallbackResolver
     * @param AppConfig $appConfig
     * @param $value
     * @param $scope
     * @param $scopeId
     * @param $path
     */
    public function __construct(
        ScopeFallbackResolverInterface $fallbackResolver,
        AppConfig $appConfig,
        $value,
        $scope,
        $scopeId,
        $path
    ) {
        $this->fallbackResolver = $fallbackResolver;
        $this->value = $value;
        $this->scope = $scope;
        $this->scopeId = $scopeId;
        $this->appConfig = $appConfig;
        $this->path = $path;
    }

    /**
     * @return bool
     */
    public function isValueChanged()
    {
        list($scope, $scopeId) = $this->fallbackResolver->getFallbackScope($this->scope, $this->scopeId);
        if ($scope) {
            return (bool)$this->value !== $this->appConfig->getValue($this->path, $scope, $scopeId);
        }
        return true;
    }
}
