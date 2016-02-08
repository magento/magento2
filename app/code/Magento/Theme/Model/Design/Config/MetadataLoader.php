<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Design\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ScopeFallbackResolverInterface;

class MetadataLoader
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var MetadataProvider
     */
    protected $metadataProvider;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var ScopeFallbackResolverInterface
     */
    protected $scopeFallbackResolver;

    /**
     * @param RequestInterface $request
     * @param MetadataProvider $metadataProvider
     * @param ScopeConfigInterface $scopeConfig
     * @param ScopeFallbackResolverInterface $scopeFallbackResolver
     */
    public function __construct(
        RequestInterface $request,
        MetadataProvider $metadataProvider,
        ScopeConfigInterface $scopeConfig,
        ScopeFallbackResolverInterface $scopeFallbackResolver
    ) {
        $this->metadataProvider = $metadataProvider;
        $this->request = $request;
        $this->scopeConfig = $scopeConfig;
        $this->scopeFallbackResolver = $scopeFallbackResolver;
    }

    /**
     * Retrieve configuration metadata
     *
     * @return array
     */
    public function getData()
    {
        $scope = $this->request->getParam('scope');
        $scopeId = $this->request->getParam('scope_id');

        $showFallbackReset = true;
        if ($scope == ScopeConfigInterface::SCOPE_TYPE_DEFAULT) {
            $showFallbackReset = false;
        }

        $data = [];
        if ($scope) {
            $metadata = $this->metadataProvider->get();
            foreach ($metadata as $key => $value) {
                $fallbackValue = $this->getFallbackValue($value['path'], $scope, $scopeId);
                $element = &$data;
                foreach (explode('/', $value['fieldset']) as $fieldset) {
                    if (!isset($element[$fieldset]['children'])) {
                        $element[$fieldset]['children'] = [];
                    }
                    $element = &$element[$fieldset]['children'];
                }
                $element[$key]['arguments']['data']['config']['default'] = $fallbackValue;
                $element[$key]['arguments']['data']['config']['showFallbackReset'] = $showFallbackReset;
            }
        }
        return $data;
    }

    /**
     * Retrieve fallback value for parent scope
     *
     * @param string $path
     * @param string $scope
     * @param string $scopeId
     * @return string
     */
    protected function getFallbackValue($path, $scope, $scopeId)
    {
        list($fallbackScope, $fallbackScopeId) = $this->scopeFallbackResolver->getFallbackScope($scope, $scopeId);
        if ($fallbackScope) {
            return (string)$this->scopeConfig->getValue($path, $fallbackScope, $fallbackScopeId);
        }
        return '';
    }
}
