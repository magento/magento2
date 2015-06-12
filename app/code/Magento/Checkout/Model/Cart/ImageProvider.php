<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Model\Cart;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ImageProvider
{
    /**
     * @var \Magento\Quote\Api\CartItemRepositoryInterface
     */
    protected $itemRepository;

    /**
     * Product repository.
     *
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Catalog\Api\ProductAttributeMediaGalleryManagementInterface
     */
    protected $productGallery;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * @var \Magento\Catalog\Model\Product\ImageFactory
     */
    protected $imageFactory;

    /**
     * @var \Magento\Framework\View\ConfigInterface
     */
    protected $viewConfig;

    /**
     * @var \Magento\Catalog\Model\Product\Image\View
     */
    protected $imageView;

    /**
     * @var \Magento\Framework\View\Design\ThemeInterface
     */
    protected $theme;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $assetRepository;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;


    /**
     * @param \Magento\Quote\Api\CartItemRepositoryInterface $itemRepository
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Api\ProductAttributeMediaGalleryManagementInterface $productGallery
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Catalog\Model\Product\Image\View $imageView
     * @param \Magento\Framework\View\ConfigInterface $viewConfig
     * @param \Magento\Framework\View\Asset\Repository $assetRepository
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\UrlInterface $urlBuilder
     */
    public function __construct(
        \Magento\Quote\Api\CartItemRepositoryInterface $itemRepository,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Api\ProductAttributeMediaGalleryManagementInterface $productGallery,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Catalog\Model\Product\Image\View $imageView,
        \Magento\Framework\View\ConfigInterface $viewConfig,
        \Magento\Framework\View\Asset\Repository $assetRepository,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->itemRepository = $itemRepository;
        $this->productRepository = $productRepository;
        $this->productGallery = $productGallery;
        $this->imageHelper = $imageHelper;
        $this->imageView = $imageView;
        $this->viewConfig = $viewConfig;
        $this->assetRepository = $assetRepository;
        $this->logger = $logger;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Get image height and width
     *
     * @return array
     */
    protected function getImageSizes()
    {
        $viewConfig = $this->viewConfig->getViewConfig();
        $imageWidth = $viewConfig->getVarValue('Magento_Catalog', 'mini_cart_product_thumbnail:width');
        $imageHeight = $viewConfig->getVarValue('Magento_Catalog', 'mini_cart_product_thumbnail:height');
        $ratio = $viewConfig->getVarValue('Magento_Catalog', 'mini_cart_product_thumbnail:ratio');

        if (!$imageWidth && $imageHeight && $ratio) {
            $imageWidth = $imageHeight * $ratio;
        }
        if (!$imageHeight && $imageWidth && $ratio) {
            $imageHeight = $imageWidth / $ratio;
        }
        return [$imageWidth, $imageHeight];
    }

    /**
     * Get placeholder URL for cart item by image type
     *
     * @param string $imageType
     * @return string
     */
    protected function getPlaceholderUrl($imageType)
    {
        $placeholderPath = 'Magento_Catalog::images/product/placeholder/' . $imageType . '.jpg';
        try {
            $url = $this->assetRepository->getUrl($placeholderPath);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $url = $this->urlBuilder->getUrl('', ['_direct' => 'core/index/notFound']);
        }
        return $url;
    }

    /**
     * {@inheritdoc}
     */
    public function getImages($cartId)
    {
        $imageUrls = [];
        list($imageWidth, $imageHeight) = $this->getImageSizes();
        $viewConfig = $this->viewConfig->getViewConfig();
        $imageType = $viewConfig->getVarValue('Magento_Catalog', 'mini_cart_product_thumbnail:type');

        /** @see code/Magento/Catalog/Helper/Product.php */
        $items = $this->itemRepository->getList($cartId);
        foreach ($items as $cartItem) {
            $product = $this->productRepository->getById($cartItem->getProductId());
            $sku = $product->getTypeInstance()->getSku($product);
            $gallery = $this->productGallery->getList($sku);
            /** @var \Magento\Catalog\Model\Product\Gallery\Entry $galleryEntry */
            foreach ($gallery as $galleryEntry) {
                if ($galleryEntry->getDisabled() == 0 && in_array($imageType, $galleryEntry->getTypes())) {
                    $this->imageHelper->init($product, $imageType, $galleryEntry->getFile());
                    $this->imageHelper->constrainOnly(true);
                    $this->imageHelper->keepAspectRatio(true);
                    $this->imageHelper->resize($imageWidth, $imageHeight);
                    $imageUrls[$cartItem->getItemId()]['src'] = (string)$this->imageHelper;
                    break;
                }
            }
            if (!array_key_exists($cartItem->getItemId(), $imageUrls)) {
                $imageUrls[$cartItem->getItemId()]['src'] = $this->getPlaceholderUrl($imageType);
            }
            $imageUrls[$cartItem->getItemId()]['width'] = $imageWidth;
            $imageUrls[$cartItem->getItemId()]['height'] = $imageHeight;
            $imageUrls[$cartItem->getItemId()]['alt'] = $cartItem->getName();
        }
        return $imageUrls;
    }
}
