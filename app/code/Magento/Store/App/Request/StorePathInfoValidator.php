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
     * Get store code from path info validate if config value. If path info is empty the try to calculate from request.
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
        $storeCode = $this->getStoreCode($pathInfo);
        if (!empty($storeCode)
            && $storeCode != Store::ADMIN_CODE
            && (bool)$this->config->getValue(\Magento\Store\Model\Store::XML_PATH_STORE_IN_URL)
        ) {
            try {
                $this->storeRepository->getActiveStoreByCode($storeCode);

                if ((bool)$this->config->getValue(
                    \Magento\Store\Model\Store::XML_PATH_STORE_IN_URL,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $storeCode
                )) {
                    return $storeCode;
                }
            } catch (NoSuchEntityException $e) {
                //return null;
            } catch (\Magento\Store\Model\StoreIsInactiveException $e) {
                //return null;
            }
        }
        return null;
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
