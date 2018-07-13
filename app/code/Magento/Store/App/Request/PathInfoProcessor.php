<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\App\Request;

/**
 * Processes the path and looks for the store in the url and removes it and modifies the path accordingly.
 */
class PathInfoProcessor implements \Magento\Framework\App\Request\PathInfoProcessorInterface
{
    /**
     * @var StorePathInfoValidator
     */
    private $storePathInfoValidator;

    /**
     * @var \Magento\Framework\App\Config\ReinitableConfigInterface
     */
    private $config;

    /**
     * @var \Magento\Store\Api\StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @param \Magento\Store\App\Request\StorePathInfoValidator $storePathInfoValidator
     * @param \Magento\Framework\App\Config\ReinitableConfigInterface $config,
     * @param \Magento\Store\Api\StoreRepositoryInterface $storeRepository
     */
    public function __construct(
        \Magento\Store\App\Request\StorePathInfoValidator $storePathInfoValidator,
        \Magento\Framework\App\Config\ReinitableConfigInterface $config,
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository
    ) {
        $this->storePathInfoValidator = $storePathInfoValidator;
        $this->config = $config;
        $this->storeRepository = $storeRepository;
    }

    /**
     * Process path info and remove store from pathInfo.
     * This method also sets request to no route if store is not valid and store is present in url config is enabled
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param string $pathInfo
     * @return string
     */
    public function process(\Magento\Framework\App\RequestInterface $request, $pathInfo) : string
    {
        if ((bool)$this->config->getValue(\Magento\Store\Model\Store::XML_PATH_STORE_IN_URL)) {
            $storeCode = $this->storePathInfoValidator->getValidStoreCode($request, $pathInfo);
            if ($storeCode) {
                try {
                    /** @var \Magento\Store\Api\Data\StoreInterface $store */
                    $this->storeRepository->getActiveStoreByCode($storeCode);
                } catch (\Magento\Store\Model\StoreIsInactiveException $e) {
                    //no route in case we're trying to access a store that's disabled
                    $request->setActionName(\Magento\Framework\App\Router\Base::NO_ROUTE);
                }

                $pathInfo = $this->trimStoreCodeFromPathInfo($pathInfo, $storeCode);
            }
        }
        return $pathInfo;
    }

    /**
     * Trim store code from path info string if exists
     *
     * @param string $pathInfo
     * @param string $storeCode
     * @return string
     */
    private function trimStoreCodeFromPathInfo(string $pathInfo, string $storeCode) : ?string
    {
        if (substr($pathInfo, 0, strlen('/' . $storeCode)) == '/'. $storeCode) {
            $pathInfo = substr($pathInfo, strlen($storeCode)+1);
        }
        return empty($pathInfo) ? '/' : $pathInfo;
    }
}
