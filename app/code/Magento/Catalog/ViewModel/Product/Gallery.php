<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\ViewModel\Product;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class Gallery implements ArgumentInterface
{
    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Block\Product\View\Gallery $block
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly StoreManagerInterface $storeManager,
        private readonly \Magento\Catalog\Block\Product\View\Gallery $block
    ) {
    }

    /**
     * Determine main product image
     *
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getMainProductImage(): ?string
    {
        $images = $this->block->getGalleryImages()->getItems();
        $mainImage = current(array_filter($images, function ($img) {
            return $this->block->isMainImage($img);
        }));

        if (!empty($images) && empty($mainImage)) {
            $mainImage = $this->block->getGalleryImages()->getFirstItem();
        }

        if ($mainImage) {
            $mainImageData = $mainImage->getData('medium_image_url');
        } else {
            return $this->block->getData('imageHelper')
                ?->getDefaultPlaceholderUrl('image');
        }

        if ($this->scopeConfig->isSetFlag(Store::XML_PATH_STORE_IN_URL)) {
            $mainImageData .= '?' . StoreManagerInterface::PARAM_NAME. '=' . $this->storeManager->getStore()->getCode();
        }

        return $mainImageData;
    }
}
