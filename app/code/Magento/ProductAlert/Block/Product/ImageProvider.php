<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductAlert\Block\Product;

use Magento\Store\Model\App\Emulation;
use Magento\Catalog\Block\Product\ImageBuilder;
use Magento\Catalog\Model\Product;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Area;
use Magento\Catalog\Block\Product\Image;

/**
 * Provides product image to be used in the Product Alert Email.
 */
class ImageProvider
{
    /**
     * @var ImageBuilder
     */
    private $imageBuilder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Emulation
     */
    private $appEmulation;

    /**
     * @param ImageBuilder $imageBuilder
     * @param StoreManagerInterface $storeManager
     * @param Emulation $appEmulation
     */
    public function __construct(
        ImageBuilder $imageBuilder,
        StoreManagerInterface $storeManager,
        Emulation $appEmulation
    ) {
        $this->imageBuilder = $imageBuilder;
        $this->storeManager = $storeManager;
        $this->appEmulation = $appEmulation;
    }

    /**
     * @param Product $product
     * @param string $imageId
     * @param array $attributes
     * @return Image
     * @throws \Exception
     */
    public function getImage(Product $product, $imageId, $attributes = [])
    {
        $storeId = $this->storeManager->getStore()->getId();
        $this->appEmulation->startEnvironmentEmulation($storeId, Area::AREA_FRONTEND, true);

        try {
            $image = $this->imageBuilder->setProduct($product)
                ->setImageId($imageId)
                ->setAttributes($attributes)
                ->create();
        } catch (\Exception $exception) {
            $this->appEmulation->stopEnvironmentEmulation();
            throw $exception;
        }

        $this->appEmulation->stopEnvironmentEmulation();
        return $image;
    }
}
