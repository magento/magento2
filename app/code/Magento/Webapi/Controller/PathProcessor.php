<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi\Controller;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;

class PathProcessor
{
    /**  Store code alias to indicate that all stores should be affected by action */
    public const ALL_STORE_CODE = 'all';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    private $localeResolver;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\ResolverInterface $localeResolver = null
    ) {
        $this->storeManager = $storeManager;
        $this->localeResolver = $localeResolver ?: ObjectManager::getInstance()->get(
            \Magento\Framework\Locale\ResolverInterface::class
        );
    }

    /**
     * Process path
     *
     * @param string $pathInfo
     * @return array
     */
    private function stripPathBeforeStorecode($pathInfo)
    {
        $pathParts = explode('/', $pathInfo !== null ? trim($pathInfo, '/') : '');
        array_shift($pathParts);
        $path = '/' . implode('/', $pathParts);
        return explode('/', ltrim($path, '/'), 2);
    }

    /**
     * Process path info
     *
     * @param string $pathInfo
     * @return string
     * @throws NoSuchEntityException
     */
    public function process($pathInfo)
    {
        $pathParts = $this->stripPathBeforeStorecode($pathInfo);
        $storeCode = current($pathParts);
        $stores = $this->storeManager->getStores(false, true);
        if (isset($stores[$storeCode])) {
            $this->storeManager->setCurrentStore($storeCode);
            $this->localeResolver->emulate($this->storeManager->getStore()->getId());
            $path = '/' . (isset($pathParts[1]) ? $pathParts[1] : '');
        } elseif ($storeCode === self::ALL_STORE_CODE) {
            $this->storeManager->setCurrentStore(\Magento\Store\Model\Store::ADMIN_CODE);
            $this->localeResolver->emulate($this->storeManager->getStore()->getId());
            $path = '/' . (isset($pathParts[1]) ? $pathParts[1] : '');
        } else {
            $path = '/' . implode('/', $pathParts);
        }
        return $path;
    }
}
