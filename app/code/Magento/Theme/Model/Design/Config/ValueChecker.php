<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Design\Config;

use Magento\Framework\App\ScopeFallbackResolverInterface;
use Magento\Framework\App\Config as AppConfig;
use Magento\Theme\Model\Design\Config\ValueProcessor;

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
     * @var ValueProcessor
     */
    protected $valueProcessor;

    /**
     * @param ScopeFallbackResolverInterface $fallbackResolver
     * @param AppConfig $appConfig
     * @param \Magento\Theme\Model\Design\Config\ValueProcessor $valueProcessor
     */
    public function __construct(
        ScopeFallbackResolverInterface $fallbackResolver,
        AppConfig $appConfig,
        ValueProcessor $valueProcessor
    ) {
        $this->fallbackResolver = $fallbackResolver;
        $this->appConfig = $appConfig;
        $this->valueProcessor = $valueProcessor;
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
            return $value !== $this->valueProcessor->process(
                $this->appConfig->getValue($path, $scope, $scopeId),
                $path
            );
        }
        return true;
    }
}
