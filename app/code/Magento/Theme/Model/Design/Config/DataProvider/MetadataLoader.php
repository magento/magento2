<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Design\Config\DataProvider;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ScopeFallbackResolverInterface;
use Magento\Theme\Api\DesignConfigRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

class MetadataLoader
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ScopeFallbackResolverInterface
     */
    protected $scopeFallbackResolver;

    /**
     * @var DesignConfigRepositoryInterface
     */
    protected $designConfigRepository;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param RequestInterface $request
     * @param ScopeFallbackResolverInterface $scopeFallbackResolver
     * @param DesignConfigRepositoryInterface $designConfigRepository
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        RequestInterface $request,
        ScopeFallbackResolverInterface $scopeFallbackResolver,
        DesignConfigRepositoryInterface $designConfigRepository,
        StoreManagerInterface $storeManager
    ) {
        $this->request = $request;
        $this->scopeFallbackResolver = $scopeFallbackResolver;
        $this->designConfigRepository = $designConfigRepository;
        $this->storeManager = $storeManager;
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

        $data = [];
        if ($scope) {
            $showFallbackReset = false;
            list($fallbackScope, $fallbackScopeId) = $this->scopeFallbackResolver->getFallbackScope($scope, $scopeId);
            if ($fallbackScope && !$this->storeManager->isSingleStoreMode()) {
                $scope = $fallbackScope;
                $scopeId = $fallbackScopeId;
                $showFallbackReset = true;
            }

            $designConfig = $this->designConfigRepository->getByScope($scope, $scopeId);
            $fieldsData = $designConfig->getExtensionAttributes()->getDesignConfigData();
            foreach ($fieldsData as $fieldData) {
                $element = &$data;
                foreach (explode('/', $fieldData->getFieldConfig()['fieldset']) as $fieldset) {
                    if (!isset($element[$fieldset]['children'])) {
                        $element[$fieldset]['children'] = [];
                    }
                    $element = &$element[$fieldset]['children'];
                }
                $fieldName = $fieldData->getFieldConfig()['field'];
                $element[$fieldName]['arguments']['data']['config']['default'] = $fieldData->getValue();
                $element[$fieldName]['arguments']['data']['config']['showFallbackReset'] = $showFallbackReset;
            }
        }
        return $data;
    }
}
