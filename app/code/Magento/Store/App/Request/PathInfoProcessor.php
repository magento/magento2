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
     * @param \Magento\Framework\App\Config\ReinitableConfigInterface $config
     * @param \Magento\Store\Api\StoreRepositoryInterface $storeRepository
     */
    public function __construct(
        \Magento\Framework\App\Config\ReinitableConfigInterface $config,
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository
    ) {
        $this->config = $config;
        $this->storeRepository = $storeRepository;
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
        $pathParts = explode('/', ltrim($pathInfo, '/'), 2);
        $storeCode = $pathParts[0];

        try {
            /** @var \Magento\Store\Api\Data\StoreInterface $store */
            $this->storeRepository->get($storeCode);
        } catch (NoSuchEntityException $e) {
            return $pathInfo;
        }

        if ((bool)$this->config->getValue(\Magento\Store\Model\Store::XML_PATH_STORE_IN_URL)
            && $request instanceof Http
            && !$request->isDirectAccessFrontendName($storeCode)
            && $storeCode != Store::ADMIN_CODE
        ) {
            $pathInfo = '/' . (isset($pathParts[1]) ? $pathParts[1] : '');
            return $pathInfo;
        } elseif (!empty($storeCode)) {
            $request->setActionName('noroute');
        }
        return $pathInfo;
    }
}
