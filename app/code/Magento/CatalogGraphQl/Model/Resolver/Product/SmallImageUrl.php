<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Product;

use Magento\Catalog\Helper\Image as CatalogImageHelper;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\GalleryFactory as GalleryResourceFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\AreaList;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Returns product's small image. If the small image is not set, returns a placeholder
 */
class SmallImageUrl implements ResolverInterface
{
    /**
     * @var GalleryResourceFactory
     */
    private $galleryResourceFactory;

    /**
     * @var AreaList
     */
    private $areaList;

    /**
     * @var CatalogImageHelper
     */
    private $catalogImageHelper;

    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ValueFactory $valueFactory
     * @param CatalogImageHelper $catalogImageHelper
     * @param AreaList $areaList
     * @param GalleryResourceFactory $galleryResourceFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ValueFactory $valueFactory,
        CatalogImageHelper $catalogImageHelper,
        AreaList $areaList,
        GalleryResourceFactory $galleryResourceFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->valueFactory = $valueFactory;
        $this->catalogImageHelper = $catalogImageHelper;
        $this->areaList = $areaList;
        $this->galleryResourceFactory = $galleryResourceFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ): Value {
        if (!isset($value['model'])) {
            $result = function () {
                return null;
            };
            return $this->valueFactory->create($result);
        }
        /** @var Product $product */
        $product = $value['model'];

        /* If small_image is not loaded for product, need to load it separately */
        if (!$product->getSmallImage()) {
            $galleryResource = $this->galleryResourceFactory->create();
            $currentStoreId =$this->storeManager->getStore()->getId();
            $productImages = $galleryResource->getProductImages($product, [$currentStoreId]);
            $productSmallImage = $this->getSmallImageFromGallery($productImages);
            $product->setSmallImage($productSmallImage);
        }

        /* Design area is necessary to return the correct storefront image URL (or a placeholder) */
        $area = $this->areaList->getArea(Area::AREA_FRONTEND);
        $area->load(Area::PART_DESIGN);

        $smallImageURL = $this->catalogImageHelper->init($product, 'product_small_image')->getUrl();
        $product->getMediaAttributes();

        $result = function () use ($smallImageURL) {
            return $smallImageURL;
        };

        return $this->valueFactory->create($result);
    }

    /**
     * Retrieves small image from the product gallery image
     *
     * @param array $productImages
     * @return string|null
     */
    private function getSmallImageFromGallery(array $productImages)
    {
        foreach ($productImages as $productImage) {
            if ($productImage['attribute_code'] == 'small_image') {
                return $productImage['filepath'];
            }
        }

        return null;
    }
}
