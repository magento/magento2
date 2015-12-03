<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Design\Config;

use Magento\Framework\App\ScopeFallbackResolverInterface;
use Magento\Framework\App\Config as AppConfig;

class ValueChecker
{
    /**
     * @var ScopeFallbackResolverInterface
     */
    protected $fallbackResolver;

    /**
     * @var AppConfig
     */
    protected $appConfig;

    /**
     * @param ScopeFallbackResolverInterface $fallbackResolver
     * @param AppConfig $appConfig
     */
    public function __construct(
        ScopeFallbackResolverInterface $fallbackResolver,
        AppConfig $appConfig
    ) {
        $this->fallbackResolver = $fallbackResolver;
        $this->appConfig = $appConfig;
    }

    /**
     * Check whether value differs from parent scope's one
     *
     * @param string $value
     * @param string $scope
     * @param int $scopeId
     * @param string $path
     * @return bool
     */
    public function isDifferentFromDefault($value, $scope, $scopeId, $path)
    {
        list($scope, $scopeId) = $this->fallbackResolver->getFallbackScope($scope, $scopeId);
        if ($scope) {
            return $value !== (string)$this->appConfig->getValue($path, $scope, $scopeId);
        }
        return true;
    }
}
