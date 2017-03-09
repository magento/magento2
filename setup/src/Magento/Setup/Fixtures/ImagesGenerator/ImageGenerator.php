<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Fixtures\ImagesGenerator;

use Magento\Framework\App\Filesystem\DirectoryList;

class ImageGenerator
{
    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var \Magento\Catalog\Model\Product\Media\Config
     */
    private $mediaConfig;

    /**
     * @var array
     */
    private $config;

    /**
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Catalog\Model\Product\Media\Config $mediaConfig
     * @param array $config
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Catalog\Model\Product\Media\Config $mediaConfig,
        array $config
    ) {
        $this->filesystem = $filesystem;
        $this->mediaConfig = $mediaConfig;
        $this->config = $config;
    }

    /**
     * Generates image from $data and puts its to /tmp folder
     *
     * @param string $data
     * @return string $imagePath
     */
    public function generate($data)
    {
        echo PHP_EOL, "Generating images...", PHP_EOL;

        $binaryData = '';
        $data = str_split(sha1($data), 2);
        foreach ($data as $item) {
            $binaryData .= base_convert($item, 16, 2);
        }
        $binaryData = str_split($binaryData, 1);

        $image = imagecreate($this->config['image-width'], $this->config['image-height']);
        $bgColor = imagecolorallocate($image, 240, 240, 240);
        $fgColor = imagecolorallocate($image, mt_rand(0, 230), mt_rand(0, 230), mt_rand(0, 230));
        $colors = [$fgColor, $bgColor];
        imagefilledrectangle($image, 0, 0, $this->config['image-width'], $this->config['image-height'], $bgColor);

        for ($row = 10; $row < 100; $row += 18) {
            for ($col = 0; $col < 90; $col += 18) {
                next($binaryData);
                imagefilledrectangle($image, $row, $col, $row + 18, $col + 18, $colors[current($binaryData)]);
            }
        }

        $mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $relativePathToMedia = $mediaDirectory->getRelativePath($this->mediaConfig->getBaseTmpMediaPath());
        $mediaDirectory->create($relativePathToMedia);

        $absolutePathToMedia = $mediaDirectory->getAbsolutePath($this->mediaConfig->getBaseTmpMediaPath());
        $imagePath = $absolutePathToMedia . DIRECTORY_SEPARATOR . $this->config['image-name'];
        imagejpeg($image, $imagePath, 100);

        return $imagePath;
    }
}
