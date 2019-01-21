<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Plugin\Model\ResourceModel\Category;

use Magento\Catalog\Model\ImageUploader;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\Catalog\Model\ResourceModel\Category\RedundantCategoryImageChecker;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Model\AbstractModel;

/**
 * Remove old Category Image file from pub/media/catalog/category directory if such Image is not used anymore.
 */
class RemoveRedundantImagePlugin
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var ImageUploader
     */
    private $imageUploader;

    /**
     * @var RedundantCategoryImageChecker
     */
    private $redundantCategoryImageChecker;

    public function __construct(
        Filesystem $filesystem,
        ImageUploader $imageUploader,
        RedundantCategoryImageChecker $redundantCategoryImageChecker
    ) {
        $this->filesystem = $filesystem;
        $this->imageUploader = $imageUploader;
        $this->redundantCategoryImageChecker = $redundantCategoryImageChecker;
    }

    /**
     * Removes Image file if it is not used anymore.
     *
     * @param CategoryResource $subject
     * @param CategoryResource $result
     * @param AbstractModel $category
     * @return CategoryResource
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(
        CategoryResource $subject,
        CategoryResource $result,
        AbstractModel $category
    ): CategoryResource {
        $originalImage = $category->getOrigData('image');
        if (null !== $originalImage
            && $originalImage !== $category->getImage()
            && $this->redundantCategoryImageChecker->execute($originalImage)
        ) {
            $basePath = $this->imageUploader->getBasePath();
            $baseImagePath = $this->imageUploader->getFilePath($basePath, $originalImage);
            /** @var \Magento\Framework\Filesystem\Directory\WriteInterface $mediaDirectory */
            $mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
            $mediaDirectory->delete($baseImagePath);
        }

        return $result;
    }
}
