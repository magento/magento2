<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Wishlist block customer item cart column
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Wishlist\Block\Customer\Wishlist\Item\Column;

use Magento\Catalog\Model\Product\Image\UrlBuilder;
use Magento\Framework\View\ConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Quote\Model\Quote\ItemFactory;
use Magento\Checkout\Block\Cart\Item\Renderer;

/**
 * @api
 * @since 100.0.2
 */
class Image extends \Magento\Wishlist\Block\Customer\Wishlist\Item\Column
{
    /** @var \Magento\Checkout\Block\Cart\Item\Renderer[] */
    private $renderers = [];

    /** @var \Magento\Quote\Model\Quote\ItemFactory  */
    private $itemFactory;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param array $data
     * @param ConfigInterface|null $config
     * @param UrlBuilder|null $urlBuilder
     * @param Renderer[] $renderers
     * @param ItemFactory|null $itemFactory
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        array $data = [],
        ConfigInterface $config = null,
        UrlBuilder $urlBuilder = null,
        array $renderers = [],
        ItemFactory $itemFactory = null
    ) {
        $this->renderers = $renderers;
        $this->itemFactory = $itemFactory ?? ObjectManager::getInstance()->get(ItemFactory::class);
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
     */
    public function getProductForThumbnail(\Magento\Wishlist\Model\Item $item)
    {
        $product = $product = $item->getProduct();
        if (isset($this->renderers[$product->getTypeId()])) {
            $quoteItem = $this->itemFactory->create(['data' => $item->getData()]);
            $quoteItem->setProduct($product);
            $quoteItem->setOptions($item->getOptions());
            return $this->renderers[$product->getTypeId()]->setItem($quoteItem)->getProductForThumbnail();
        }
        return $product;
    }
}
