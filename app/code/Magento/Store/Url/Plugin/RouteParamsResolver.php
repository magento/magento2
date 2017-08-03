<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Url\Plugin;

use \Magento\Store\Model\Store;
use \Magento\Store\Model\ScopeInterface as StoreScopeInterface;

/**
 * Plugin for \Magento\Framework\Url\RouteParamsResolver
 * @since 2.0.0
 */
class RouteParamsResolver
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     * @since 2.0.0
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Url\QueryParamsResolverInterface
     * @since 2.0.0
     */
    protected $queryParamsResolver;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Url\QueryParamsResolverInterface $queryParamsResolver
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Url\QueryParamsResolverInterface $queryParamsResolver
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->queryParamsResolver = $queryParamsResolver;
    }

    /**
     * Process scope query parameters.
     *
     * @param \Magento\Framework\Url\RouteParamsResolver $subject
     * @param array $data
     * @param bool $unsetOldParams
     * @return array
     * @since 2.2.0
     */
    public function beforeSetRouteParams(
        \Magento\Framework\Url\RouteParamsResolver $subject,
        array $data,
        $unsetOldParams = true
    ) {
        if (isset($data['_scope'])) {
            $subject->setScope($data['_scope']);
            unset($data['_scope']);
        }
        if (isset($data['_scope_to_url']) && (bool)$data['_scope_to_url'] === true) {
            $storeCode = $subject->getScope() ?: $this->storeManager->getStore()->getCode();
            $useStoreInUrl = $this->scopeConfig->getValue(
                Store::XML_PATH_STORE_IN_URL,
                StoreScopeInterface::SCOPE_STORE,
                $storeCode
            );
            if (!$useStoreInUrl && !$this->storeManager->hasSingleStore()) {
                $this->queryParamsResolver->setQueryParam('___store', $storeCode);
            }
        }
        unset($data['_scope_to_url']);

        return [$data, $unsetOldParams];
    }
}
