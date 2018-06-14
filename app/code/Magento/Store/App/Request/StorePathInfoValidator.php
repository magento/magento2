<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\App\Request;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\Store;

/**
 * Processes the path and looks for the store in the url and removes it and modifies the request accordingly.
 */
class StorePathInfoValidator
{
    /**
     * Store Config
     *
     * @var \Magento\Framework\App\Config\ReinitableConfigInterface
     */
    private $config;

    /**
     * @var \Magento\Store\Api\StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @var \Magento\Framework\App\Request\PathInfo
     */
    private $pathInfo;

    /**
     * @param \Magento\Framework\App\Config\ReinitableConfigInterface $config
     * @param \Magento\Store\Api\StoreRepositoryInterface $storeRepository
     * @param \Magento\Framework\App\Request\PathInfo $pathInfo
     */
    public function __construct(
        \Magento\Framework\App\Config\ReinitableConfigInterface $config,
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository,
        \Magento\Framework\App\Request\PathInfo $pathInfo
    ) {
        $this->config = $config;
        $this->storeRepository = $storeRepository;
        $this->pathInfo = $pathInfo;
    }

    /**
     * Get store code from path info in request
     *
     * @param \Magento\Framework\App\Request\Http $request
     * @param string $pathInfo
     * @return string|null
     */
    public function getStoreFrontCodeFromPathInfo(
        \Magento\Framework\App\Request\Http $request,
        string $pathInfo
    ) : ?string {
        if (!empty($pathInfo)) {
            return $this->getValidStoreCode($request, $pathInfo);
        }
        return null;
    }

    /**
     * Get path info from request
     *
     * @param \Magento\Framework\App\Request\Http $request
     * @return string
     */
    public function getPathInfo(
        \Magento\Framework\App\Request\Http $request
    ) : string {
        return $this->pathInfo->getPathInfo($request->getRequestUri(), $request->getBaseUrl());
    }

    /**
     * Get store code if rules apply and validate it if config value is enabled and if not return no route
     *
     * @param \Magento\Framework\App\Request\Http $request
     * @param string $pathInfo
     * @return string|null
     */
    public function getValidStoreCode(
        \Magento\Framework\App\Request\Http $request,
        string $pathInfo
    ) : ?string {
        $pathParts = explode('/', ltrim($pathInfo, '/'), 2);
        $storeCode = current($pathParts);
        if (!$request->isDirectAccessFrontendName($storeCode)
            && !empty($storeCode)
            && $storeCode != Store::ADMIN_CODE
        ) {
            try {
                /** @var \Magento\Store\Api\Data\StoreInterface $store */
                $this->storeRepository->getActiveStoreByCode($storeCode);
            } catch (NoSuchEntityException $e) {
                return null;
            }

            if ((bool)$this->config->getValue(
                \Magento\Store\Model\Store::XML_PATH_STORE_IN_URL,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeCode
            )
            ) {
                return $storeCode;
            } else {
                $request->setActionName(\Magento\Framework\App\Router\Base::NO_ROUTE);
            }
        }
        return null;
    }
}
