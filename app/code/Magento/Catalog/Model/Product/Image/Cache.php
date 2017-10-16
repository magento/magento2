<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Image;

use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\Product;
use Magento\Theme\Model\ResourceModel\Theme\Collection as ThemeCollection;
use Magento\Theme\Model\Config\Customization as ThemeCustomizationConfig;
use Magento\Framework\App\Area;
use Magento\Framework\View\ConfigInterface;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\ObjectManagerInterface;

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
     * @var Config
     */
    protected $eavConfig;

    /**
     * @var Attribute
     */
    protected $attribute;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

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
        ThemeCustomizationConfig $themeCustomizationConfig,
        Config $eavConfig,
        Attribute $attribute,
        ObjectManagerInterface $objectManager
    ) {
        $this->viewConfig = $viewConfig;
        $this->themeCollection = $themeCollection;
        $this->imageHelper = $imageHelper;
        $this->themeCustomizationConfig = $themeCustomizationConfig;
        $this->eavConfig = $eavConfig;
        $this->attribute = $attribute;
        $this->objectManager = $objectManager;
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
            $themesInUse = $this->getThemesInUse();

            /** @var \Magento\Theme\Model\Theme $theme */
            foreach ($themesInUse as $theme) {
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

    /**
     * Get themes in use
     *
     * @return array
     */
    public function getThemesInUse()
    {
        $themesInUse = [];

        $registeredThemes = $this->themeCollection->loadRegisteredThemes();
        $storesByThemes   = $this->themeCustomizationConfig->getStoresByThemes();

        foreach ($registeredThemes as $registeredTheme) {
            if (array_key_exists($registeredTheme->getThemeId(), $storesByThemes)) {
                $themesInUse[] = $registeredTheme;
            }
        }

        $resource = $this->objectManager->get(\Magento\Framework\App\ResourceConnection::class);

        $productCustomDesignAttributeId = $this->attribute->loadByCode(4, 'custom_design')->getId();

        $productSql = $resource->getConnection()
            ->select()
            ->from(
                ['eav' => $resource->getTableName('catalog_product_entity_varchar')],
                ['value']
            )
            ->where('eav.attribute_id = ?', $productCustomDesignAttributeId)
            ->where('eav.value > 0')
            ->group('value');

        $productThemeIds = $resource->getConnection()->fetchCol($productSql);

        foreach ($productThemeIds as $productThemeId) {
            if (array_key_exists($productThemeId, $storesByThemes)
                && !array_key_exists($productThemeId, $themesInUse) ) {
                $themesInUse[] = $this->themeCollection->load($productThemeId);
            }
        }

        $categoryCustomDesignAttributeId = $this->attribute->loadByCode(3, 'custom_design')->getId();

        $categorySql = $resource->getConnection()
            ->select()
            ->from(
                ['eav' => $resource->getTableName('catalog_category_entity_varchar')],
                ['value']
            )
            ->where('eav.attribute_id = ?', $categoryCustomDesignAttributeId)
            ->where('eav.value > 0')
            ->group('value');

        $categoryThemeIds = $resource->getConnection()->fetchCol($categorySql);

        foreach ($categoryThemeIds as $categoryThemeId) {
            if (array_key_exists($categoryThemeId, $storesByThemes)
                && !array_key_exists($categoryThemeId, $themesInUse) ) {
                $themesInUse[] = $this->themeCollection->load($categoryThemeId);
            }
        }

        $pageSql = $resource->getConnection()
            ->select()
            ->from(
                ['page' => $resource->getTableName('cms_page')],
                ['custom_theme']
            )
            ->where('custom_theme > 0')
            ->group('custom_theme');

        $pageThemeIds = $resource->getConnection()->fetchCol($pageSql);

        foreach ($pageThemeIds as $pageThemeId) {
            if (array_key_exists($pageThemeId, $storesByThemes)
                && !array_key_exists($pageThemeId, $themesInUse) ) {
                $themesInUse[] = $this->themeCollection->load($pageThemeId);
            }
        }

        return $themesInUse;
    }
}
