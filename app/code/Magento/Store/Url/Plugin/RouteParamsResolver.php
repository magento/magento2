<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Store\Url\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Url\RouteParamsResolver as UrlRouteParamsResolver;
use Magento\Framework\Url\QueryParamsResolverInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface as StoreScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Store;

/**
 * Plugin for \Magento\Framework\Url\RouteParamsResolver
 */
class RouteParamsResolver
{
    /**
     * Initialize dependencies.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param QueryParamsResolverInterface $queryParamsResolver
     */
    public function __construct(
        protected ScopeConfigInterface $scopeConfig,
        protected StoreManagerInterface $storeManager,
        protected QueryParamsResolverInterface $queryParamsResolver
    ) {
    }

    /**
     * Process scope query parameters.
     *
     * @param \Magento\Framework\Url\RouteParamsResolver $subject
     * @param array $data
     * @param bool $unsetOldParams
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @return array
     */
    public function beforeSetRouteParams(UrlRouteParamsResolver $subject, array $data, $unsetOldParams = true)
    {
        if (isset($data['_scope'])) {
            $subject->setScope($data['_scope']);
            unset($data['_scope']);
        }
        if (isset($data['_scope_to_url']) && (bool)$data['_scope_to_url'] === true) {
            $useStoreInUrl = $this->scopeConfig->isSetFlag(Store::XML_PATH_STORE_IN_URL);
            if (!$useStoreInUrl && !$this->storeManager->hasSingleStore()) {
                $currentScope = $subject->getScope();
                $storeCode = $currentScope instanceof StoreInterface
                    ? $currentScope->getCode()
                    : $this->storeManager->getStore()->getCode();
                $this->queryParamsResolver->setQueryParam('___store', $storeCode);
            }
        }
        unset($data['_scope_to_url']);

        return [$data, $unsetOldParams];
    }
}
