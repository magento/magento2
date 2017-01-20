<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Website;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\Website\Link as ProductWebsiteLink;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class SaveHandler
 * @package Magento\Catalog\Model\Product\Website
 */
class SaveHandler implements ExtensionInterface
{
    /** @var  ProductWebsiteLink */
    private $productWebsiteLink;

    /** @var  StoreManagerInterface */
    private $storeManager;

    /**
     * SaveHandler constructor.
     * @param ProductWebsiteLink $productWebsiteLink
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ProductWebsiteLink $productWebsiteLink,
        StoreManagerInterface $storeManager
    ) {
        $this->productWebsiteLink = $productWebsiteLink;
        $this->storeManager = $storeManager;
    }

    /**
     * Get website ids from extension attributes and persist them
     * @param ProductInterface $product
     * @param array $arguments
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return ProductInterface
     */
    public function execute($product, $arguments = [])
    {
        if ($this->storeManager->isSingleStoreMode()) {
            $defaultWebsiteId = $this->storeManager->getDefaultStoreView()->getWebsiteId();
            $websiteIds = [$defaultWebsiteId];
        } else {
            $extensionAttributes = $product->getExtensionAttributes();
            $websiteIds = $extensionAttributes->getWebsiteIds();
        }

        if ($websiteIds !== null) {
            $this->productWebsiteLink->saveWebsiteIds($product, $websiteIds);
        }

        return $product;
    }
}
