<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Ui\DataProvider\Product\Collector;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductRender\ButtonInterfaceFactory;
use Magento\Catalog\Api\Data\ProductRenderExtensionFactory;
use Magento\Catalog\Api\Data\ProductRenderExtensionInterface;
use Magento\Catalog\Api\Data\ProductRenderInfoDtoInterface;
use Magento\Catalog\Api\Data\ProductRenderInterface;
use Magento\Catalog\Model\ProductRenderInfoDto;
use Magento\Catalog\Ui\DataProvider\Product\ProductRenderCollectorInterface;
use Magento\Catalog\Ui\DataProvider\Product\ProductRenderInfoProviderInterface;
use Magento\Wishlist\Helper\Data;

/**
 * Collect information needed to render wishlist button on front
 */
class Button implements ProductRenderCollectorInterface
{
    /** Url Key */
    const KEY_WISHLIST_URL_PARAMS = "wishlist_url_params";

    /**
     * @param Data $wishlistHelper
     * @param ProductRenderExtensionFactory $productRenderExtensionFactory
     * @param ButtonInterfaceFactory $buttonInterfaceFactory
     */
    public function __construct(
        private readonly Data $wishlistHelper,
        private readonly ProductRenderExtensionFactory $productRenderExtensionFactory,
        private readonly ButtonInterfaceFactory $buttonInterfaceFactory
    ) {
    }

    /**
     * @inheritdoc
     */
    public function collect(ProductInterface $product, ProductRenderInterface $productRender)
    {
        /** @var ProductRenderExtensionInterface $extensionAttributes */
        $extensionAttributes = $productRender->getExtensionAttributes();

        if (!$extensionAttributes) {
            $extensionAttributes = $this->productRenderExtensionFactory->create();
        }

        $button = $this->buttonInterfaceFactory->create();
        $button->setUrl($this->wishlistHelper->getAddParams($product));
        $extensionAttributes->setWishlistButton($button);
        $productRender->setExtensionAttributes($extensionAttributes);
    }
}
