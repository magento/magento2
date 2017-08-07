<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Design\Config\DataProvider;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ScopeFallbackResolverInterface;
use Magento\Theme\Api\DesignConfigRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class \Magento\Theme\Model\Design\Config\DataProvider\MetadataLoader
 *
 * @since 2.1.0
 */
class MetadataLoader
{
    /**
     * @var RequestInterface
     * @since 2.1.0
     */
    protected $request;

    /**
     * @var ScopeFallbackResolverInterface
     * @since 2.1.0
     */
    protected $scopeFallbackResolver;

    /**
     * @var DesignConfigRepositoryInterface
     * @since 2.1.0
     */
    protected $designConfigRepository;

    /**
     * @var StoreManagerInterface
     * @since 2.1.0
     */
    protected $storeManager;

    /**
     * @param RequestInterface $request
     * @param ScopeFallbackResolverInterface $scopeFallbackResolver
     * @param DesignConfigRepositoryInterface $designConfigRepository
     * @param StoreManagerInterface $storeManager
     * @since 2.1.0
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
     * @since 2.1.0
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
