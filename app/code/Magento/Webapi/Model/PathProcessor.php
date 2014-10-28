<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Webapi\Model;

use Magento\Framework\Exception\NoSuchEntityException;

class PathProcessor
{
    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     */
    public function __construct(\Magento\Framework\StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    /**
     * Process path
     *
     * @param string $pathInfo
     * @return array
     */
    private function stripPathBeforeStorecode($pathInfo)
    {
        $pathParts = explode('/', trim($pathInfo, '/'));
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
        $storeCode = $pathParts[0];
        $stores = $this->storeManager->getStores(false, true);
        if (isset($stores[$storeCode])) {
            $this->storeManager->setCurrentStore($storeCode);
            $path = '/' . (isset($pathParts[1]) ? $pathParts[1] : '');
        } else {
            $this->storeManager->setCurrentStore(\Magento\Store\Model\Store::DEFAULT_CODE);
            $path = '/' . implode('/', $pathParts);
        }
        return $path;
    }
}
