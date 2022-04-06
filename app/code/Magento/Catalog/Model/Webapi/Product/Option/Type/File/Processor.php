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
     * Saves file
     *
     * @param ImageContentInterface $imageContent
     * @return string
     * @throws \Magento\Framework\Exception\InputException
     */
    protected function saveFile(ImageContentInterface $imageContent)
    {
        $filePath = $this->imageProcessor->processImageContent($this->destinationFolder, $imageContent);
        return $this->destinationFolder . $filePath;
    }

    /**
     * Save file content and return file details
     *
     * @param ImageContentInterface $imageContent
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function processFileContent(ImageContentInterface $imageContent)
    {
        $filePath = $this->saveFile($imageContent);

        $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $fileAbsolutePath = $mediaDirectory->getAbsolutePath($filePath);
        $fileContent = $mediaDirectory->readFile($filePath);
        $fileHash = hash('sha256', $fileContent);
        $imageSize = getimagesizefromstring($fileContent);
        $stat = $mediaDirectory->stat($fileAbsolutePath);
        $result = [
            'type' => $imageContent->getType(),
            'title' => $imageContent->getName(),
            'fullpath' => $fileAbsolutePath,
            'quote_path' => $filePath,
            'order_path' => $filePath,
            'size' => $stat['size'],
            'width' => $imageSize ? $imageSize[0] : 0,
            'height' => $imageSize ? $imageSize[1] : 0,
            'secret_key' => substr($fileHash, 0, 20),
        ];
        return $result;
    }
}
