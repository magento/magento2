<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Category;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Catalog\Model\Category\FileInfo;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;

/**
 * Resolve category image to a fully qualified URL
 */
class Image implements ResolverInterface
{
    /** @var DirectoryList  */
    private $directoryList;

    /** @var FileInfo  */
    private $fileInfo;

    /**
     * @param DirectoryList $directoryList
     * @param FileInfo $fileInfo
     */
    public function __construct(
        DirectoryList $directoryList,
        FileInfo $fileInfo
    ) {
        $this->directoryList = $directoryList;
        $this->fileInfo = $fileInfo;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        /** @var \Magento\Catalog\Model\Category $category */
        $category = $value['model'];
        $imagePath = $category->getData('image');
        if (empty($imagePath)) {
            return null;
        }
        /** @var StoreInterface $store */
        $store = $context->getExtensionAttributes()->getStore();
        $baseUrl = $store->getBaseUrl();

        $filenameWithMedia =  $this->fileInfo->isBeginsWithMediaDirectoryPath($imagePath)
            ? $imagePath : $this->formatFileNameWithMediaCategoryFolder($imagePath);

        if (!$this->fileInfo->isExist($filenameWithMedia)) {
            throw new GraphQlInputException(__('Category image not found.'));
        }

        // return full url
        return rtrim($baseUrl, '/') . $filenameWithMedia;
    }

    /**
     * Format category media folder to filename
     *
     * @param string $fileName
     * @return string
     */
    private function formatFileNameWithMediaCategoryFolder(string $fileName): string
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $baseFileName = basename($fileName);
        return '/'
            . $this->directoryList->getUrlPath('media')
            . '/'
            . ltrim(FileInfo::ENTITY_MEDIA_PATH, '/')
            . '/'
            . $baseFileName;
    }
}
