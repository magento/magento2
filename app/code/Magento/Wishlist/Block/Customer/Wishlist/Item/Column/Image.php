<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Block\Customer\Wishlist\Item\Column;

<<<<<<< HEAD
=======
use Magento\Catalog\Model\Product\Image\UrlBuilder;
use Magento\Framework\View\ConfigInterface;
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
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
<<<<<<< HEAD
    /**
     * @var ItemResolverInterface
     */
=======
    /** @var ItemResolverInterface */
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
    private $itemResolver;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\App\Http\Context $httpContext
<<<<<<< HEAD
     * @param array|null $data
=======
     * @param array $data
     * @param ConfigInterface|null $config
     * @param UrlBuilder|null $urlBuilder
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     * @param ItemResolverInterface|null $itemResolver
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        array $data = [],
<<<<<<< HEAD
=======
        ConfigInterface $config = null,
        UrlBuilder $urlBuilder = null,
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
        ItemResolverInterface $itemResolver = null
    ) {
        $this->itemResolver = $itemResolver ?: ObjectManager::getInstance()->get(ItemResolverInterface::class);
        parent::__construct(
            $context,
            $httpContext,
<<<<<<< HEAD
            $data
=======
            $data,
            $config,
            $urlBuilder
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
        );
    }

    /**
     * Identify the product from which thumbnail should be taken.
     *
<<<<<<< HEAD
     * @param \Magento\Wishlist\Model\Item $item
     * @return \Magento\Catalog\Model\Product
     */
    public function getProductForThumbnail(\Magento\Wishlist\Model\Item $item): \Magento\Catalog\Model\Product
=======
     * @return \Magento\Catalog\Model\Product
     */
    public function getProductForThumbnail(\Magento\Wishlist\Model\Item $item) : \Magento\Catalog\Model\Product
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
    {
        return $this->itemResolver->getFinalProduct($item);
    }
}
