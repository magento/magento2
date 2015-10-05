<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Webapi\Product\Option\Type\File;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Api\Data\ImageContentInterface;
use Magento\Catalog\Model\Product\Option\Type\File\ValidateFactory;
use Magento\Framework\Api\ImageProcessor;
use Magento\Framework\Filesystem;

class Processor
{
    /** @var Filesystem */
    protected $filesystem;

    /** @var ImageProcessor  */
    protected $imageProcessor;

    /** @var ValidateFactory  */
    protected $validateFactory;

    /** @var string */
    protected $fileFullPath;

    /**
     * @param Filesystem $filesystem
     * @param ValidateFactory $validateFactory
     * @param ImageProcessor $imageProcessor
     */
    public function __construct(
        Filesystem $filesystem,
        ValidateFactory $validateFactory,
        ImageProcessor $imageProcessor
    ) {
        $this->filesystem = $filesystem;
        $this->imageProcessor = $imageProcessor;
        $this->validateFactory = $validateFactory;
    }

    /**
     * @param ImageContentInterface $imageContent
     * @param string $destinationFolder
     */
    protected function saveFile(ImageContentInterface $imageContent, $destinationFolder)
    {
        $uri = $this->filesystem->getUri(DirectoryList::MEDIA);
        $filePath = $this->imageProcessor->processImageContent($destinationFolder, $imageContent);
        $this->fileFullPath = $uri . $destinationFolder . $filePath;
    }

    /**
     * @param ImageContentInterface $imageContent
     * @param \Magento\Catalog\Model\Product\Option $option
     * @param $destinationFolder
     * @return bool|array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function processFileContent(ImageContentInterface $imageContent, $option, $destinationFolder)
    {
        $this->saveFile($imageContent, $destinationFolder);

        $fileAbsolutePath = $this->filesystem->getDirectoryRead(DirectoryList::ROOT)
            ->getAbsolutePath($this->fileFullPath);
        $imageSize = getimagesize($fileAbsolutePath);
        $width = $imageSize ? $imageSize[0] : 0;
        $height = $imageSize ? $imageSize[1] : 0;
        $fileHash = md5($fileAbsolutePath);
        $result = [
            'type' => $imageContent->getType(),
            'title' => $imageContent->getName(),
            'fullpath' => $fileAbsolutePath,
            'size' => filesize($fileAbsolutePath),
            'width' => $width,
            'height' => $height,
            'secret_key' => substr($fileHash, 0, 20),
        ];
        if ($destinationFolder == '/custom_options/quote') {
            $result['quote_path'] = $this->fileFullPath;
        } else {
            $result['order_path'] = $this->fileFullPath;
        }
        return $result;
    }
}
