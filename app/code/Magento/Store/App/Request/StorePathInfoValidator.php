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
 * Gets the store from the path if valid
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
     * Find the store in the path info if valid and trim it from the path info
     *
     * @param \Magento\Framework\App\Request\Http $request
     * @param string $pathInfo
     * @return string
     */
    public function trimValidStoreFromPathInfo(
        \Magento\Framework\App\Request\Http $request,
        string $pathInfo
    ) : ?string {
        $storeCode = $this->getValidStoreCode($request, $pathInfo);
        if ($storeCode) {
            $pathParts = $this->splitPathInfo($pathInfo);
            if (count($pathParts) > 1) {
                return '/' . (isset($pathParts[1]) ? $pathParts[1] : '');
            }
        }
        return null;
    }

    /**
     * Get store code from path info validate if config value. If pathinfo is empty the try to calculate from request.
     * This method also sets request to no route if store doesn't have url enabled but store in url is enabled globally.
     *
     * @param \Magento\Framework\App\Request\Http $request
     * @param string $pathInfo
     * @return string|null
     */
    public function getValidStoreCode(
        \Magento\Framework\App\Request\Http $request,
        string $pathInfo = ''
    ) : ?string {
        if (empty($pathInfo)) {
            $pathInfo = $this->pathInfo->getPathInfo(
                $request->getRequestUri(),
                $request->getBaseUrl()
            );
        }
        $storeCode = current($this->splitPathInfo($pathInfo));
        if (!$request->isDirectAccessFrontendName($storeCode)
            && !empty($storeCode)
            && $storeCode != Store::ADMIN_CODE
            && (bool)$this->config->getValue(\Magento\Store\Model\Store::XML_PATH_STORE_IN_URL)
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

    /**
     * @param string $pathInfo
     * @return array
     */
    private function splitPathInfo(string $pathInfo) : array
    {
        return explode('/', ltrim($pathInfo, '/'), 2);
    }
}
