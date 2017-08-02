<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Ui\DataProvider\Product\Listing\Collector;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductRender\ImageInterface;
use Magento\Catalog\Api\Data\ProductRender\ImageInterfaceFactory;
use Magento\Catalog\Api\Data\ProductRenderInterface;
use Magento\Catalog\Helper\ImageFactory;
use Magento\Catalog\Ui\DataProvider\Product\ProductRenderCollectorInterface;
use Magento\Framework\App\State;
use Magento\Framework\View\DesignInterface;
use Magento\Store\Model\StoreManager;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Collect enough information about image rendering on front
 * If you want to add new image, that should render on front you need
 * to configure this class in di.xml
 *
 * @since 2.2.0
 */
class Image implements ProductRenderCollectorInterface
{
    /** Key for image information access to, when render product */
    const KEY = "images";

    /**
     * @var ImageFactory
     * @since 2.2.0
     */
    private $imageFactory;

    /**
     * @var array
     * @since 2.2.0
     */
    private $imageCodes;

    /**
     * @var State
     * @since 2.2.0
     */
    private $state;

    /**
     * @var StoreManager
     * @since 2.2.0
     */
    private $storeManager;

    /**
     * @var DesignInterface
     * @since 2.2.0
     */
    private $design;

    /**
     * @var ImageInterfaceFactory
     * @since 2.2.0
     */
    private $imageRenderInfoFactory;

    /**
     * Image constructor.
     * @param ImageFactory $imageFactory
     * @param State $state
     * @param StoreManager|StoreManagerInterface $storeManager
     * @param DesignInterface $design
     * @param ImageInterfaceFactory $imageRenderInfoFactory
     * @param array $imageCodes
     * @since 2.2.0
     */
    public function __construct(
        ImageFactory $imageFactory,
        State $state,
        StoreManagerInterface $storeManager,
        DesignInterface $design,
        ImageInterfaceFactory $imageRenderInfoFactory,
        array $imageCodes = []
    ) {
        $this->imageFactory = $imageFactory;
        $this->imageCodes = $imageCodes;
        $this->state = $state;
        $this->storeManager = $storeManager;
        $this->design = $design;
        $this->imageRenderInfoFactory = $imageRenderInfoFactory;
    }

    /**
     * In order to allow to use image generation using Services, we need to emulate area code and store code
     *
     * @inheritdoc
     * @since 2.2.0
     */
    public function collect(ProductInterface $product, ProductRenderInterface $productRender)
    {
        $images = [];

        foreach ($this->imageCodes as $imageCode) {
            /** @var ImageInterface $image */
            $image = $this->imageRenderInfoFactory->create();
            /** @var \Magento\Catalog\Helper\Image $helper */
            $helper = $this->state
                ->emulateAreaCode(
                    'frontend',
                    [$this, "emulateImageCreating"],
                    [$product, $imageCode, (int) $productRender->getStoreId(), $image]
                );
            $resizedInfo = $helper->getResizedImageInfo();

            $image->setCode($imageCode);
            $image->setHeight($helper->getHeight());
            $image->setWidth($helper->getWidth());
            $image->setLabel($helper->getLabel());
            $image->setResizedHeight($resizedInfo[1]);
            $image->setResizedWidth($resizedInfo[0]);

            $images[] = $image;
        }

        $productRender->setImages($images);
    }

    /**
     * Callback in which we emulate initialize default design theme, depends on current store, be settings store id
     * from render info
     *
     * @param ProductInterface $product
     * @param string $imageCode
     * @param int $storeId
     * @param ImageInterface $image
     * @return \Magento\Catalog\Helper\Image
     * @since 2.2.0
     */
    public function emulateImageCreating(ProductInterface $product, $imageCode, $storeId, ImageInterface $image)
    {
        $this->storeManager->setCurrentStore($storeId);
        $this->design->setDefaultDesignTheme();

        $imageHelper = $this->imageFactory->create();
        $imageHelper->init($product, $imageCode);
        $image->setUrl($imageHelper->getUrl());
        return $imageHelper;
    }
}
