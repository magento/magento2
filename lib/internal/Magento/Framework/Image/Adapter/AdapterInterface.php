<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Image\Adapter;

/**
 * Interface \Magento\Framework\Image\Adapter\AdapterInterface
 *
 */
interface AdapterInterface
{
    /**
     * Adapter type
     */
    const ADAPTER_GD2 = 'GD2';

    const ADAPTER_IM = 'IMAGEMAGICK';

    /**
     * Returns rgba array of the specified pixel
     *
     * @param int $x
     * @param int $y
     * @return array
     */
    public function getColorAt($x, $y);

    /**
     * Render image and return its binary contents
     *
     * @see \Magento\Framework\Image\Adapter\AbstractAdapter::getImage
     * @return string
     */
    public function getImage();

    /**
     * Add watermark to image
     *
     * @param string $imagePath
     * @param int $positionX
     * @param int $positionY
     * @param int $opacity
     * @param bool $tile
     * @return void
     */
    public function watermark($imagePath, $positionX = 0, $positionY = 0, $opacity = 30, $tile = false);

    /**
     * Reassign image dimensions
     *
     * @return void
     */
    public function refreshImageDimensions();

    /**
     * Checks required dependencies
     *
     * @return void
     * @throws \Exception If some of dependencies are missing
     */
    public function checkDependencies();

    /**
     * Create Image from string
     *
     * @param string $text
     * @param string $font
     * @return \Magento\Framework\Image\Adapter\AbstractAdapter
     */
    public function createPngFromString($text, $font = '');

    /**
     * Open image for processing
     *
     * @param string $filename
     * @return void
     */
    public function open($filename);

    /**
     * Change the image size
     *
     * @param null|int $frameWidth
     * @param null|int $frameHeight
     * @return void
     */
    public function resize($frameWidth = null, $frameHeight = null);

    /**
     * Crop image
     *
     * @param int $top
     * @param int $left
     * @param int $right
     * @param int $bottom
     * @return bool
     */
    public function crop($top = 0, $left = 0, $right = 0, $bottom = 0);

    /**
     * Save image to specific path.
     *
     * If some folders of path does not exist they will be created
     *
     * @param null|string $destination
     * @param null|string $newName
     * @return void
     * @throws \Exception  If destination path is not writable
     */
    public function save($destination = null, $newName = null);

    /**
     * Rotate image on specific angle
     *
     * @param int $angle
     * @return void
     */
    public function rotate($angle);
}
