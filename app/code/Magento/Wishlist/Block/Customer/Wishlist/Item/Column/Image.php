<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Block\Customer\Wishlist\Item\Column;

use Magento\Catalog\Model\Product\Image\UrlBuilder;
use Magento\Framework\View\ConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Catalog\Model\Product\Configuration\Item\ItemResolverInterface;

/**
 * Wishlist block customer item cart column
 *
 * @api
 * @since 100.0.2
 */
class Image extends \Magento\Wishlist\Block\Customer\Wishlist\Item\Column
{
    /** @var ItemResolverInterface */
    private $itemResolver;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param array $data
     * @param ConfigInterface|null $config
     * @param UrlBuilder|null $urlBuilder
     * @param ItemResolverInterface|null $itemResolver
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        array $data = [],
        ConfigInterface $config = null,
        UrlBuilder $urlBuilder = null,
        ItemResolverInterface $itemResolver = null
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
     * @return \Magento\Catalog\Model\Product
     * @since 101.0.5
     */
    public function getProductForThumbnail(\Magento\Wishlist\Model\Item $item) : \Magento\Catalog\Model\Product
    {
        return $this->itemResolver->getFinalProduct($item);
    }
}
