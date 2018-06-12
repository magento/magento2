<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\App\Request;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\Store;
use Magento\Framework\App\Request\Http;

/**
 * Processes the path and looks for the store in the url and and removes it and modifies the request accordingly
 * Users of this class can compare the para
 */
class PathInfoProcessor implements \Magento\Framework\App\Request\PathInfoProcessorInterface
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
     * Process path info and remove store from pathInfo or redirect to noroute
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param string $pathInfo
     * @return string
     */
    public function process(\Magento\Framework\App\RequestInterface $request, $pathInfo) : string
    {
        if ($this->getAndValidateStoreFrontStoreCode($request, $pathInfo)) {
            $pathParts = explode('/', ltrim($pathInfo, '/'), 2);
            $pathInfo = '/' . (isset($pathParts[1]) ? $pathParts[1] : '');
        }
        return $pathInfo;
    }

    /**
     * Compute store from path info in request
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return string
     */
    public function resolveStoreFrontStoreFromPathInfo(
        \Magento\Framework\App\RequestInterface $request
    ) : ?string {
        if ($request instanceof \Magento\Framework\App\Request\Http) {
            $pathInfo = $this->pathInfo->computePathInfo($request->getRequestUri(), $request->getBaseUrl());
            if (!empty($pathInfo)) {
                return $this->getAndValidateStoreFrontStoreCode($request, $pathInfo);
            }
        }
        return null;
    }

    /**
     * Get store code and validate it if config value is enabled and if not in directFrontNames return no route
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param string $pathInfo
     * @return null|string
     */
    private function getAndValidateStoreFrontStoreCode(
        \Magento\Framework\App\RequestInterface $request,
        string $pathInfo
    ) : ?string {
        if ((bool)$this->config->getValue(\Magento\Store\Model\Store::XML_PATH_STORE_IN_URL)) {
            $pathParts = explode('/', ltrim($pathInfo, '/'), 2);
            $storeCode = $pathParts[0];

            try {
                /** @var \Magento\Store\Api\Data\StoreInterface $store */
                $this->storeRepository->getActiveStoreByCode($storeCode);
            } catch (NoSuchEntityException $e) {
                return null;
            }

            if ($request instanceof Http
                && !$request->isDirectAccessFrontendName($storeCode)
                && $storeCode != Store::ADMIN_CODE
            ) {
                return $storeCode;
            } elseif (!empty($storeCode)) {
                $request->setActionName(\Magento\Framework\App\Router\Base::NO_ROUTE);
            }
        }
        return null;
    }
}
