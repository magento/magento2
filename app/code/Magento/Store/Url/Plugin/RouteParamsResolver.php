<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Url\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Url\QueryParamsResolverInterface;
use Magento\Framework\Url\RouteParamsResolver as UrlRouteParamsResolver;
use \Magento\Store\Model\Store;
use \Magento\Store\Api\Data\StoreInterface;
use \Magento\Store\Model\ScopeInterface as StoreScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

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
        protected readonly ScopeConfigInterface $scopeConfig,
        protected readonly StoreManagerInterface $storeManager,
        protected readonly QueryParamsResolverInterface $queryParamsResolver
    ) {
    }

    /**
     * Process scope query parameters.
     *
     * @param UrlRouteParamsResolver $subject
     * @param array $data
     * @param bool $unsetOldParams
     * @return array
     *@throws NoSuchEntityException
     *
     */
    public function beforeSetRouteParams(
        UrlRouteParamsResolver $subject,
        array $data,
        $unsetOldParams = true
    ) {
        if (isset($data['_scope'])) {
            $subject->setScope($data['_scope']);
            unset($data['_scope']);
        }
        if (isset($data['_scope_to_url']) && (bool)$data['_scope_to_url'] === true) {
            /** @var StoreInterface $currentScope */
            $currentScope = $subject->getScope();
            $storeCode = $currentScope && $currentScope instanceof StoreInterface ?
                $currentScope->getCode() :
                $this->storeManager->getStore()->getCode();

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
