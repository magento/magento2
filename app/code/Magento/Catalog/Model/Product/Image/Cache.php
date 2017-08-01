<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Image;

use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\Product;
use Magento\Theme\Model\ResourceModel\Theme\Collection as ThemeCollection;
use Magento\Framework\App\Area;
use Magento\Framework\View\ConfigInterface;

/**
 * Class \Magento\Catalog\Model\Product\Image\Cache
 *
 * @since 2.0.0
 */
class Cache
{
    /**
     * @var ConfigInterface
     * @since 2.0.0
     */
    protected $viewConfig;

    /**
     * @var ThemeCollection
     * @since 2.0.0
     */
    protected $themeCollection;

    /**
     * @var ImageHelper
     * @since 2.0.0
     */
    protected $imageHelper;

    /**
     * @var array
     * @since 2.0.0
     */
    protected $data = [];

    /**
     * @param ConfigInterface $viewConfig
     * @param ThemeCollection $themeCollection
     * @param ImageHelper $imageHelper
     * @since 2.0.0
     */
    public function __construct(
        ConfigInterface $viewConfig,
        ThemeCollection $themeCollection,
        ImageHelper $imageHelper
    ) {
        $this->viewConfig = $viewConfig;
        $this->themeCollection = $themeCollection;
        $this->imageHelper = $imageHelper;
    }

    /**
     * Retrieve view configuration data
     *
     * Collect data for 'Magento_Catalog' module from /etc/view.xml files.
     *
     * @return array
     * @since 2.0.0
     */
    protected function getData()
    {
        if (!$this->data) {
            /** @var \Magento\Theme\Model\Theme $theme */
            foreach ($this->themeCollection->loadRegisteredThemes() as $theme) {
                $config = $this->viewConfig->getViewConfig([
                    'area' => Area::AREA_FRONTEND,
                    'themeModel' => $theme,
                ]);
                $images = $config->getMediaEntities('Magento_Catalog', ImageHelper::MEDIA_TYPE_CONFIG_NODE);
                foreach ($images as $imageId => $imageData) {
                    $this->data[$theme->getCode() . $imageId] = array_merge(['id' => $imageId], $imageData);
                }
            }
        }
        return $this->data;
    }

    /**
     * Resize product images and save results to image cache
     *
     * @param Product $product
     * @return $this
     * @since 2.0.0
     */
    public function generate(Product $product)
    {
        $galleryImages = $product->getMediaGalleryImages();
        if ($galleryImages) {
            foreach ($galleryImages as $image) {
                foreach ($this->getData() as $imageData) {
                    $this->processImageData($product, $imageData, $image->getFile());
                }
            }
        }
        return $this;
    }

    /**
     * Process image data
     *
     * @param Product $product
     * @param array $imageData
     * @param string $file
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @since 2.0.0
     */
    protected function processImageData(Product $product, array $imageData, $file)
    {
        $this->imageHelper->init($product, $imageData['id'], $imageData);
        $this->imageHelper->setImageFile($file);

        if (isset($imageData['aspect_ratio'])) {
            $this->imageHelper->keepAspectRatio($imageData['aspect_ratio']);
        }
        if (isset($imageData['frame'])) {
            $this->imageHelper->keepFrame($imageData['frame']);
        }
        if (isset($imageData['transparency'])) {
            $this->imageHelper->keepTransparency($imageData['transparency']);
        }
        if (isset($imageData['constrain'])) {
            $this->imageHelper->constrainOnly($imageData['constrain']);
        }
        if (isset($imageData['background'])) {
            $this->imageHelper->backgroundColor($imageData['background']);
        }

        $this->imageHelper->save();

        return $this;
    }
}
