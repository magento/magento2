<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Image;

use Magento\Framework\View\ConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Builds parameters array used to build Image Asset
 */
class ParamsBuilder
{
    /**
     * @var int
     */
    private $defaultQuality = 80;

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
    private $keepFrame = true;

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
     * @var ConfigInterface
     */
    private $presentationConfig;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ConfigInterface $presentationConfig
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ConfigInterface $presentationConfig, ScopeConfigInterface $scopeConfig)
    {
        $this->presentationConfig = $presentationConfig->getViewConfig();
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param array $imageArguments
     * @return array
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function build(array $imageArguments)
    {
        $type = isset($imageArguments['type']) ? $imageArguments['type'] : null;

        $width = isset($imageArguments['width']) ? $imageArguments['width'] : null;
        $height = isset($imageArguments['height']) ? $imageArguments['height'] : null;

        $frame = !empty($imageArguments['frame'])
            ? $imageArguments['frame']
            : $this->keepFrame;

        $constrain = !empty($imageArguments['constrain'])
            ? $imageArguments['constrain']
            : $this->defaultConstrainOnly;

        $aspectRatio = !empty($imageArguments['aspect_ratio'])
            ? $imageArguments['aspect_ratio']
            : $this->defaultKeepAspectRatio;

        $transparency = !empty($imageArguments['transparency'])
            ? $imageArguments['transparency']
            : $this->defaultKeepTransparency;

        $background = !empty($imageArguments['background'])
            ? $imageArguments['background']
            : $this->defaultBackground;

        $miscParams = [
            'image_type' => $type,
            'image_height' => $height,
            'image_width' => $width,
            'keep_aspect_ratio' => ($aspectRatio ? '' : 'non') . 'proportional',
            'keep_frame' => ($frame ? '' : 'no') . 'frame',
            'keep_transparency' => ($transparency ? '' : 'no') . 'transparency',
            'constrain_only' => ($constrain ? 'do' : 'not') . 'constrainonly',
            'background' => $this->rgbToString($background),
            'angle' => !empty($imageArguments['angle']) ? $imageArguments['angle'] : $this->defaultAngle,
            'quality' => $this->defaultQuality
        ];

        $watermarkFile = $this->scopeConfig->getValue(
            "design/watermark/{$type}_image",
            ScopeInterface::SCOPE_STORE
        );

        if ($watermarkFile) {
            $watermarkSize = $this->scopeConfig->getValue(
                "design/watermark/{$type}_size",
                ScopeInterface::SCOPE_STORE
            );

            $miscParams['watermark_file'] = $watermarkFile;
            $miscParams['watermark_image_opacity'] = $this->scopeConfig->getValue(
                "design/watermark/{$type}_imageOpacity",
                ScopeInterface::SCOPE_STORE
            );
            $miscParams['watermark_position'] = $this->scopeConfig->getValue(
                "design/watermark/{$type}_position",
                ScopeInterface::SCOPE_STORE
            );
            $miscParams['watermark_width'] = !empty($watermarkSize['width']) ? $watermarkSize['width'] : null;
            $miscParams['watermark_height'] = !empty($watermarkSize['width']) ? $watermarkSize['height'] : null;
        }

        return $miscParams;
    }

    /**
     * Convert array of 3 items (decimal r, g, b) to string of their hex values
     *
     * @param int[] $rgbArray
     * @return string
     */
    public function rgbToString($rgbArray)
    {
        $result = [];
        foreach ($rgbArray as $value) {
            if (null === $value) {
                $result[] = 'null';
            } else {
                $result[] = sprintf('%02s', dechex($value));
            }
        }
        return implode($result);
    }
}
