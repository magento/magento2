<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Design\Config;

use Magento\Framework\App\Config as AppConfig;
use Magento\Framework\App\Config\Modular;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ScopeFallbackResolverInterface;

/**
 * Class ValueChecker
 */
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
     * Loads config.xml files only
     * @var AppConfig
     */
    protected AppConfig $modularConfig;

    /**
     * @param ScopeFallbackResolverInterface $fallbackResolver
     * @param AppConfig $appConfig
     * @param ValueProcessor $valueProcessor
     * @param AppConfig|null $modularConfig
     */
    public function __construct(
        ScopeFallbackResolverInterface $fallbackResolver,
        AppConfig $appConfig,
        ValueProcessor $valueProcessor,
        ?AppConfig $modularConfig = null
    ) {
        $this->fallbackResolver = $fallbackResolver;
        $this->appConfig = $appConfig;
        $this->valueProcessor = $valueProcessor;
        $this->modularConfig = $modularConfig ?: ObjectManager::getInstance()->get(Modular::class);
    }

    /**
     * Check whether value differs from default
     *
     * @param string $value
     * @param string $scope
     * @param int $scopeId
     * @param array $fieldConfig
     * @return bool
     */
    public function isDifferentFromDefault($value, $scope, $scopeId, array $fieldConfig): bool
    {
        [$parentScope, $parentScopeId] = $this->fallbackResolver->getFallbackScope($scope, $scopeId);

        $configPath = $fieldConfig['path'];
        $defaultValue = $this->modularConfig->getValue($configPath, $scope, $scopeId) ?? '';
        $newValueProcessed = $this->valueProcessor->process($value, $parentScope, $parentScopeId, $fieldConfig);
        $defaultValueProcessed = $this->valueProcessor->process(
            $defaultValue,
            $parentScope,
            $parentScopeId,
            $fieldConfig
        );

        if (($defaultValueProcessed === '') && ($parentScope)) {
            $defaultValue = $this->appConfig->getValue($configPath, $parentScope, $parentScopeId) ?? '';
            $defaultValueProcessed = $this->valueProcessor->process(
                $defaultValue,
                $parentScope,
                $parentScopeId,
                $fieldConfig
            );
        }

        $isDefaultValue = $this->isEqual($newValueProcessed, $defaultValueProcessed);

        return !$isDefaultValue;
    }

    /**
     * Compare two variables
     *
     * @param mixed $value
     * @param mixed $defaultValue
     * @return bool
     */
    protected function isEqual($value, $defaultValue)
    {
        if (is_array($value)) {
            return $this->isEqualArrays($value, $defaultValue);
        }

        return $value === $defaultValue;
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
        if (count($value) !== count($defaultValue)) {
            return false;
        }
        foreach ($value as $key => $elem) {
            if (is_array($elem)) {
                if (isset($defaultValue[$key])) {
                    $result = $result && $this->isEqualArrays($elem, $defaultValue[$key]);
                } else {
                    return false;
                }
            } else {
                if (isset($defaultValue[$key])) {
                    $result = $result && ($defaultValue[$key] == $elem);
                } else {
                    return false;
                }
            }
        }
        return $result;
    }
}
