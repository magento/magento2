<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\App\Request;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Request\PathInfo;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreIsInactiveException;

/**
 * Gets the store from the path if valid
 */
class StorePathInfoValidator
{
    /**
     * Store Config
     *
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @var PathInfo
     */
    private $pathInfo;

    /**
     * @param ScopeConfigInterface $config
     * @param StoreRepositoryInterface $storeRepository
     * @param PathInfo $pathInfo
     */
    public function __construct(
        ScopeConfigInterface $config,
        StoreRepositoryInterface $storeRepository,
        PathInfo $pathInfo
    ) {
        $this->config = $config;
        $this->storeRepository = $storeRepository;
        $this->pathInfo = $pathInfo;
    }

    /**
     * Get store code from path info validate if config value. If path info is empty the try to calculate from request.
     *
     * @param Http $request
     * @param string $pathInfo
     * @return string|null
     */
    public function getValidStoreCode(Http $request, string $pathInfo = '') : ?string
    {
        $useStoreCodeInUrl = (bool) $this->config->getValue(Store::XML_PATH_STORE_IN_URL);
        if (!$useStoreCodeInUrl) {
            return null;
        }

        if (empty($pathInfo)) {
            $pathInfo = $this->pathInfo->getPathInfo($request->getRequestUri(), $request->getBaseUrl());
        }
        $storeCode = $this->getStoreCode($pathInfo);
        if (empty($storeCode) || $storeCode === Store::ADMIN_CODE) {
            return null;
        }

        try {
            $this->storeRepository->getActiveStoreByCode($storeCode);

            return $storeCode;
        } catch (NoSuchEntityException $e) {
            return null;
        } catch (StoreIsInactiveException $e) {
            return null;
        }
    }

    /**
     * Get store code from path info string
     *
     * @param string $pathInfo
     * @return string
     */
    private function getStoreCode(string $pathInfo) : string
    {
        $pathParts = explode('/', ltrim($pathInfo, '/'), 2);
        return current($pathParts);
    }
}
