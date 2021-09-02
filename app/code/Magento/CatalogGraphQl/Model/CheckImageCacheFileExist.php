<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model;

use Magento\Catalog\Model\Product\Image;
use Magento\Catalog\Model\Product\ImageFactory;
use Magento\Catalog\Model\Product\Image\ParamsBuilder;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Service check if image cache file exist
 */
class CheckImageCacheFileExist
{
    /**
     * @var Filesystem\Directory\WriteInterface
     */
    private $mediaDirectory;

    /**
     * @var ParamsBuilder
     */
    private $paramsBuilder;

    /**
     * @var ImageFactory
     */
    private $productImageFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ImageFactory $imageFactory
     * @param Filesystem $filesystem
     * @param ParamsBuilder $paramsBuilder
     * @param StoreManagerInterface $storeManager
     * @throws FileSystemException
     */
    public function __construct(
        ImageFactory $imageFactory,
        Filesystem $filesystem,
        ParamsBuilder $paramsBuilder,
        StoreManagerInterface $storeManager
    ) {
        $this->productImageFactory = $imageFactory;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->paramsBuilder = $paramsBuilder;
        $this->storeManager = $storeManager;
    }

    /**
     * Save image cache file if is not exist
     *
     * @param string $imgUrl
     * @param string $imagePath
     * @param string $imageType
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function execute(
        string $imgUrl,
        string $imagePath,
        string $imageType
    ): string {
        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(DirectoryList::MEDIA);
        $filePath = str_replace($mediaUrl, '', $imgUrl);
        if ($this->mediaDirectory->isFile($filePath)) {
            return $imgUrl;
        }
        $image = $this->productImageFactory->create();
        $image->setDestinationSubdir($imageType)
            ->setBaseFile($imagePath);
        if ($image->isBaseFilePlaceholder()) {
            return $imgUrl;
        }
        $params = $this->getImageParams($image);
        if (isset($params['watermark_file'])) {
            $image->setWatermark(
                $params['watermark_file'],
                $params['watermark_position'],
                [
                    'width'  => $params['watermark_width'],
                    'height' => $params['watermark_height']
                ],
                $params['watermark_width'],
                $params['watermark_height'],
                $params['watermark_image_opacity']
            );
            $image->saveFile();
        }

        return $imgUrl;
    }

    /**
     * Get image params
     *
     * @param Image $image
     * @return array
     */
    private function getImageParams(Image $image)
    {
        return $this->paramsBuilder->build(
            [
                'type' => $image->getDestinationSubdir(),
                'width' => $image->getWidth(),
                'height' => $image->getHeight(),
                'quality' => $image->getQuality()
            ]
        );
    }
}
