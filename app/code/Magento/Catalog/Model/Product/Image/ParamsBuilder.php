<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Image;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\ConfigInterface;
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
     * @param ScopeConfigInterface $scopeConfig
     * @param ConfigInterface $viewConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ConfigInterface $viewConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->viewConfig = $viewConfig;
    }

    /**
     * Build image params
     *
     * @param array $imageArguments
     * @return array
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function build(array $imageArguments): array
    {
        $miscParams = [
            'image_type' => $imageArguments['type'] ?? null,
            'image_height' => $imageArguments['height'] ?? null,
            'image_width' => $imageArguments['width'] ?? null,
        ];

        $overwritten = $this->overwriteDefaultValues($imageArguments);
        $watermark = isset($miscParams['image_type']) ? $this->getWatermark($miscParams['image_type']) : [];

        return array_merge($miscParams, $overwritten, $watermark);
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
     * @return array
     */
    private function getWatermark(string $type): array
    {
        $file = $this->scopeConfig->getValue(
            "design/watermark/{$type}_image",
            ScopeInterface::SCOPE_STORE
        );

        if ($file) {
            $size = $this->scopeConfig->getValue(
                "design/watermark/{$type}_size",
                ScopeInterface::SCOPE_STORE
            );
            $opacity = $this->scopeConfig->getValue(
                "design/watermark/{$type}_imageOpacity",
                ScopeInterface::SCOPE_STORE
            );
            $position = $this->scopeConfig->getValue(
                "design/watermark/{$type}_position",
                ScopeInterface::SCOPE_STORE
            );
            $width = !empty($size['width']) ? $size['width'] : null;
            $height = !empty($size['height']) ? $size['height'] : null;

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
        return (bool) $this->viewConfig->getViewConfig(['area' => \Magento\Framework\App\Area::AREA_FRONTEND])
            ->getVarValue('Magento_Catalog', 'product_image_white_borders');
    }
}
