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
            return !$this->isEqual(
                $this->valueProcessor->process(
                    $value,
                    $path
                ),
                $this->valueProcessor->process(
                    $this->appConfig->getValue($path, $scope, $scopeId),
                    $path
                )
            );
        }
        return true;
    }

    /**
     * Compare two variables
     *
     * @param $value
     * @param $defaultValue
     * @return bool
     */
    protected function isEqual ($value, $defaultValue)
    {
        switch (gettype($value)) {
            case 'string':
                return $value === $defaultValue;
            case 'array':
                return $this->isEqualArrays($value, $defaultValue);
        }
        return false;
    }

    /**
     * Compare two multidimensional arrays
     *
     * @param array $value
     * @param array $defaultValue
     * @return bool
     */
    protected function isEqualArrays(array $value, array $defaultValue)
    {
        $result = true;
        foreach ($value as $key => $elem) {
            if (is_array($elem)) {
                if (isset($defaultValue[$key])) {
                    $result = $result && $this->isEqualArrays($elem, $defaultValue[$key]);
                } else {
                    return false;
                }
            } else {
                if (isset($defaultValue[$key])) {
                    $result = $result && ($defaultValue[$key] === $elem);
                } else {
                    return false;
                }
            }
        }
        return $result;
    }
}
