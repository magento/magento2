<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Image;

use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\Product;
use Magento\Theme\Model\ResourceModel\Theme\Collection as ThemeCollection;
use Magento\Theme\Model\Config\Customization as ThemeCustomizationConfig;
use Magento\Framework\App\Area;
use Magento\Framework\View\ConfigInterface;

class Cache
{
    /**
     * @var ConfigInterface
     */
    protected $viewConfig;

    /**
     * @var ThemeCollection
     */
    protected $themeCollection;

    /**
     * @var ThemeCustomizationConfig
     */
    protected $themeCustomizationConfig;

    /**
     * @var ImageHelper
     */
    protected $imageHelper;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @param ConfigInterface $viewConfig
     * @param ThemeCollection $themeCollection
     * @param ImageHelper $imageHelper
     */
    public function __construct(
        ConfigInterface $viewConfig,
        ThemeCollection $themeCollection,
        ImageHelper $imageHelper,
        ThemeCustomizationConfig $themeCustomizationConfig
    ) {
        $this->viewConfig = $viewConfig;
        $this->themeCollection = $themeCollection;
        $this->themeCustomizationConfig = $themeCustomizationConfig;
        $this->imageHelper = $imageHelper;
    }

    /**
     * Retrieve view configuration data
     *
     * Collect data for 'Magento_Catalog' module from /etc/view.xml files.
     *
     * @return array
     */
    protected function getData()
    {
        if (!$this->data) {
            $themes = $this->getThemesInUse();

            /** @var \Magento\Theme\Model\Theme $theme */
            foreach ($themes as $theme) {
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

    protected function getThemesInUse()
    {
        $themesInUse = [];

        $registeredThemes = $this->themeCollection->loadRegisteredThemes();
        $storesByThemes   = $this->themeCustomizationConfig->getStoresByThemes();

        foreach ($registeredThemes as $registeredTheme) {
            if (array_key_exists($registeredTheme->getThemeId(), $storesByThemes)) {
                $themesInUse[] = $registeredTheme;
            }
        }

        return $themesInUse;
    }
}
