<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Block\Customer\Wishlist\Item\Column;

use Magento\Catalog\Block\Product\Context as ProductContext;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Image\UrlBuilder;
use Magento\Framework\App\Http\Context;
use Magento\Framework\View\ConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Catalog\Model\Product\Configuration\Item\ItemResolverInterface;
use Magento\Wishlist\Block\Customer\Wishlist\Item\Column;
use Magento\Wishlist\Model\Item;

/**
 * Wishlist block customer item cart column
 *
 * @api
 * @since 100.0.2
 */
class Image extends Column
{
    /**
     * @param ProductContext $context
     * @param Context $httpContext
     * @param array $data
     * @param ConfigInterface|null $config
     * @param UrlBuilder|null $urlBuilder
     * @param ItemResolverInterface|null $itemResolver
     */
    public function __construct(
        ProductContext $context,
        Context $httpContext,
        array $data = [],
        ConfigInterface $config = null,
        UrlBuilder $urlBuilder = null,
        private ?ItemResolverInterface $itemResolver = null
    ) {
        $this->itemResolver = $itemResolver ?: ObjectManager::getInstance()->get(ItemResolverInterface::class);
        parent::__construct(
            $context,
            $httpContext,
            $data,
            $config,
            $urlBuilder
        );
    }

    /**
     * Identify the product from which thumbnail should be taken.
     *
     * @return Product
     * @since 101.0.5
     */
    public function getProductForThumbnail(Item $item): Product
    {
        return $this->itemResolver->getFinalProduct($item);
    }
}
