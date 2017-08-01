<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\App\Request;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class \Magento\Store\App\Request\PathInfoProcessor
 *
 * @since 2.0.0
 */
class PathInfoProcessor implements \Magento\Framework\App\Request\PathInfoProcessorInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    private $storeManager;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @since 2.0.0
     */
    public function __construct(\Magento\Store\Model\StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    /**
     * Process path info
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param string $pathInfo
     * @return string
     * @since 2.0.0
     */
    public function process(\Magento\Framework\App\RequestInterface $request, $pathInfo)
    {
        $pathParts = explode('/', ltrim($pathInfo, '/'), 2);
        $storeCode = $pathParts[0];

        try {
            /** @var \Magento\Store\Api\Data\StoreInterface $store */
            $store = $this->storeManager->getStore($storeCode);
        } catch (NoSuchEntityException $e) {
            return $pathInfo;
        }

        if ($store->isUseStoreInUrl()) {
            if (!$request->isDirectAccessFrontendName($storeCode)) {
                $this->storeManager->setCurrentStore($storeCode);
                $pathInfo = '/' . (isset($pathParts[1]) ? $pathParts[1] : '');
                return $pathInfo;
            } elseif (!empty($storeCode)) {
                $request->setActionName('noroute');
                return $pathInfo;
            }
            return $pathInfo;
        }
        return $pathInfo;
    }
}
