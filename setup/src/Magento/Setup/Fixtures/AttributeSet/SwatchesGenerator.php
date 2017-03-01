<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Fixtures\AttributeSet;

use Magento\Swatches\Model\Swatch;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Generates data for creating Visual Swatch attributes of "image" and "color" types.
 */
class SwatchesGenerator
{
    /**
     * Generated swatch image width in pixels.
     *
     * @var int
     */
    const GENERATED_SWATCH_WIDTH = 110;

    /**
     * Generated swatch image height in pixels.
     *
     * @var int
     */
    const GENERATED_SWATCH_HEIGHT = 90;

    /**
     * File name for temporary swatch image file.
     *
     * @var string
     */
    const GENERATED_SWATCH_TMP_NAME = 'tmp_swatch.jpg';

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var \Magento\Catalog\Model\Product\Media\Config
     */
    private $mediaConfig;

    /**
     * @var \Magento\Swatches\Helper\Media
     */
    private $swatchHelper;

    /**
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Catalog\Model\Product\Media\Config $config
     * @param \Magento\Swatches\Helper\Media $swatchHelper
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Catalog\Model\Product\Media\Config $config,
        \Magento\Swatches\Helper\Media $swatchHelper
    ) {
        $this->filesystem = $filesystem;
        $this->mediaConfig = $config;
        $this->swatchHelper = $swatchHelper;
    }

    /**
     * Generates data for Swatch Attribute of the required type
     *
     * @param int $optionCount
     * @param string $data
     * @param string $type
     * @return array
     */
    public function generateSwatchData($optionCount, $data, $type)
    {
        if ($type === null) {
            return [];
        }

        $attribute['swatch_input_type'] = Swatch::SWATCH_INPUT_TYPE_VISUAL;
        $attribute['swatchvisual']['value'] = array_reduce(
            range(1, $optionCount),
            function ($values, $index) use ($optionCount, $data, $type) {
                if ($type === 'image') {
                    $values['option_' . $index] = $this->generateSwatchImage($data . $index);
                }
                if ($type === 'color') {
                    $values['option_' . $index] = $this->generateSwatchColor($index / $optionCount);
                }
                return $values;
            },
            []
        );
        $attribute['optionvisual']['value'] = array_reduce(
            range(1, $optionCount),
            function ($values, $index) use ($optionCount) {
                $values['option_' . $index] = ['option ' . $index];
                return $values;
            },
            []
        );

        return $attribute;
    }

    /**
     * Generate hex-coded color for Swatch Attribute based on provided index
     *
     * Colors will change gradually according to index value.
     *
     * @param int $index
     * @return string
     */
    private function generateSwatchColor($index)
    {
        return '#' . str_repeat(dechex(255 * $index), 3);
    }

    /**
     * Generate and save image for Swatch Attribute
     *
     * Image is generated with a set background color rgb(240, 240, 240), random foreground color, and pattern which
     * is based on the binary representation of $data.
     *
     * @param string $data String value to be used for generation.
     * @return string Path to the image file.
     */
    private function generateSwatchImage($data)
    {
        $binaryData = '';
        $data = str_split(sha1($data), 2);
        foreach ($data as $item) {
            $binaryData .= base_convert($item, 16, 2);
        }
        $binaryData = str_split($binaryData, 1);

        $image = imagecreate(self::GENERATED_SWATCH_WIDTH, self::GENERATED_SWATCH_HEIGHT);
        $bgColor = imagecolorallocate($image, 240, 240, 240);
        $fgColor = imagecolorallocate($image, mt_rand(0, 230), mt_rand(0, 230), mt_rand(0, 230));
        $colors = [$fgColor, $bgColor];
        imagefilledrectangle($image, 0, 0, self::GENERATED_SWATCH_WIDTH, self::GENERATED_SWATCH_HEIGHT, $bgColor);

        for ($row = 10; $row < 100; $row += 18) {
            for ($col = 0; $col < 90; $col += 18) {
                next($binaryData);
                imagefilledrectangle($image, $row, $col, $row + 18, $col + 18, $colors[current($binaryData)]);
            }
        }

        $mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $absolutePathToMedia = $mediaDirectory->getAbsolutePath($this->mediaConfig->getBaseTmpMediaPath());
        $relativePathToMedia = $mediaDirectory->getRelativePath($this->mediaConfig->getBaseTmpMediaPath());
        $mediaDirectory->create($relativePathToMedia);

        imagejpeg($image, $absolutePathToMedia . DIRECTORY_SEPARATOR . self::GENERATED_SWATCH_TMP_NAME, 100);
        $imagePath = substr($this->swatchHelper->moveImageFromTmp(self::GENERATED_SWATCH_TMP_NAME), 1);
        $this->swatchHelper->generateSwatchVariations($imagePath);

        return $imagePath;
    }
}
