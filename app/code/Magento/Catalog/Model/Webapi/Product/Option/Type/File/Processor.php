<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Webapi\Product\Option\Type\File;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Api\Data\ImageContentInterface;
use Magento\Framework\Api\ImageProcessor;
use Magento\Framework\Filesystem;

class Processor
{
    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\Framework\Api\ImageProcessor
     */
    protected $imageProcessor;

    /**
     * @var string
     */
    protected $destinationFolder = 'custom_options/quote';

    /**
     * @param Filesystem $filesystem
     * @param ImageProcessor $imageProcessor
     */
    public function __construct(
        Filesystem $filesystem,
        ImageProcessor $imageProcessor
    ) {
        $this->filesystem = $filesystem;
        $this->imageProcessor = $imageProcessor;
    }

    /**
     * @param ImageContentInterface $imageContent
     * @return string
     */
    protected function saveFile(ImageContentInterface $imageContent)
    {
        $filePath = $this->imageProcessor->processImageContent($this->destinationFolder, $imageContent);
        return $this->destinationFolder . $filePath;
    }

    /**
     * @param ImageContentInterface $imageContent
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function processFileContent(ImageContentInterface $imageContent)
    {
        $filePath = $this->saveFile($imageContent);

        $fileAbsolutePath = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath($filePath);
        $fileHash = md5($this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->readFile($filePath));
        $imageSize = getimagesize($fileAbsolutePath);
        $result = [
            'type' => $imageContent->getType(),
            'title' => $imageContent->getName(),
            'fullpath' => $fileAbsolutePath,
            'quote_path' => $filePath,
            'order_path' => $filePath,
            'size' => filesize($fileAbsolutePath),
            'width' => $imageSize ? $imageSize[0] : 0,
            'height' => $imageSize ? $imageSize[1] : 0,
            'secret_key' => substr($fileHash, 0, 20),
        ];
        return $result;
    }
}
