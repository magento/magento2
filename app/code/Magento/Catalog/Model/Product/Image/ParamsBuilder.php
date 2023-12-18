<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Image;

use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\ConfigInterface;
use Magento\Framework\View\Design\Theme\FlyweightFactory;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Catalog\Model\Product\Image;

/**
 * Builds parameters array used to build Image Asset
 */
class ParamsBuilder
{
    /**
     * @var array
     */
    private $defaultBackground = [255, 255, 255];

    /**
     * @var int|null
     */
    private $defaultAngle = null;

    /**
     * @var bool
     */
    private $defaultKeepAspectRatio = true;

    /**
     * @var bool
     */
    private $defaultKeepTransparency = true;

    /**
     * @var bool
     */
    private $defaultConstrainOnly = true;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ConfigInterface
     */
    private $viewConfig;

    /**
     * @var DesignInterface
     */
    private $design;

    /**
     * @var FlyweightFactory
     */
    private $themeFactory;

    /**
     * @var ThemeInterface
     */
    private $currentTheme;

    /**
     * @var array
     */
    private $themesList = [];

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param ConfigInterface $viewConfig
     * @param DesignInterface|null $designInterface
     * @param FlyweightFactory|null $themeFactory
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ConfigInterface $viewConfig,
        DesignInterface $designInterface = null,
        FlyweightFactory $themeFactory = null
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->viewConfig = $viewConfig;
        $this->design = $designInterface ?? ObjectManager::getInstance()->get(DesignInterface::class);
        $this->themeFactory = $themeFactory ?? ObjectManager::getInstance()->get(FlyweightFactory::class);
    }

    /**
     * Build image params
     *
     * @param array $imageArguments
     * @param int $scopeId
     * @return array
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function build(array $imageArguments, int $scopeId = null): array
    {
        $this->determineCurrentTheme($scopeId);

        $miscParams = [
            'image_type' => $imageArguments['type'] ?? null,
            'image_height' => $imageArguments['height'] ?? null,
            'image_width' => $imageArguments['width'] ?? null,
        ];

        $overwritten = $this->overwriteDefaultValues($imageArguments);
        $watermark = isset($miscParams['image_type']) ? $this->getWatermark($miscParams['image_type'], $scopeId) : [];

        return array_merge($miscParams, $overwritten, $watermark);
    }

    /**
     * Determine the theme assigned to passed scope id
     *
     * @param int|null $scopeId
     * @return void
     */
    private function determineCurrentTheme(int $scopeId = null): void
    {
        if (is_numeric($scopeId) || !$this->currentTheme) {
            $themeId = $this->design->getConfigurationDesignTheme(Area::AREA_FRONTEND, ['store' => $scopeId]);
            if (isset($this->themesList[$themeId])) {
                $this->currentTheme = $this->themesList[$themeId];
            } else {
                $this->currentTheme = $this->themeFactory->create($themeId);
                $this->themesList[$themeId] = $this->currentTheme;
            }
        }
    }

    /**
     * Overwrite default values
     *
     * @param array $imageArguments
     * @return array
     */
    private function overwriteDefaultValues(array $imageArguments): array
    {
        $frame = $imageArguments['frame'] ?? $this->hasDefaultFrame();
        $constrain = $imageArguments['constrain'] ?? $this->defaultConstrainOnly;
        $aspectRatio = $imageArguments['aspect_ratio'] ?? $this->defaultKeepAspectRatio;
        $transparency = $imageArguments['transparency'] ?? $this->defaultKeepTransparency;
        $background = $imageArguments['background'] ?? $this->defaultBackground;
        $angle = $imageArguments['angle'] ?? $this->defaultAngle;
        $quality = (int) $this->scopeConfig->getValue(Image::XML_PATH_JPEG_QUALITY);

        return [
            'background' => (array) $background,
            'angle' => $angle,
            'quality' => $quality,
            'keep_aspect_ratio' => (bool) $aspectRatio,
            'keep_frame' => (bool) $frame,
            'keep_transparency' => (bool) $transparency,
            'constrain_only' => (bool) $constrain,
        ];
    }

    /**
     * Get watermark
     *
     * @param string $type
     * @param int $scopeId
     * @return array
     */
    private function getWatermark(string $type, int $scopeId = null): array
    {
        $file = $this->scopeConfig->getValue(
            "design/watermark/{$type}_image",
            ScopeInterface::SCOPE_STORE,
            $scopeId
        );

        if ($file) {
            $size = explode(
                'x',
                (string) $this->scopeConfig->getValue(
                    "design/watermark/{$type}_size",
                    ScopeInterface::SCOPE_STORE,
                    $scopeId
                )
            );
            $opacity = $this->scopeConfig->getValue(
                "design/watermark/{$type}_imageOpacity",
                ScopeInterface::SCOPE_STORE,
                $scopeId
            );
            $position = $this->scopeConfig->getValue(
                "design/watermark/{$type}_position",
                ScopeInterface::SCOPE_STORE,
                $scopeId
            );
            $width = !empty($size['0']) ? $size['0'] : null;
            $height = !empty($size['1']) ? $size['1'] : null;

            return [
                'watermark_file' => $file,
                'watermark_image_opacity' => $opacity,
                'watermark_position' => $position,
                'watermark_width' => $width,
                'watermark_height' => $height
            ];
        }

        return [];
    }

    /**
     * Get frame from product_image_white_borders
     *
     * @return bool
     */
    private function hasDefaultFrame(): bool
    {
        return (bool) $this->viewConfig->getViewConfig(
            [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'themeModel' => $this->currentTheme
            ]
        )->getVarValue('Magento_Catalog', 'product_image_white_borders');
    }
}
