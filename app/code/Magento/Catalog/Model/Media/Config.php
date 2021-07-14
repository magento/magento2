<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Media;

use Magento\Catalog\Model\Category\FileInfo;
use Magento\Catalog\Model\Product\Media\ConfigInterface as ProductMediaConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\MediaStorage\Model\File\Storage\Request;
use Magento\MediaStorage\Model\Media\ConfigInterface;

/**
 * Media path config for Catalog.
 */
class Config implements ConfigInterface
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var ProductMediaConfigInterface
     */
    private $productMediaConfig;

    /**
     * @param Request $request
     * @param ProductMediaConfigInterface $productMediaConfig
     */
    public function __construct(
        Request $request,
        ProductMediaConfigInterface $productMediaConfig
    ) {
        $this->request = $request;
        $this->productMediaConfig = $productMediaConfig;
    }

    /**
     * @inheritdoc
     */
    public function getBaseMediaPath(): string
    {
        $relativePath = $this->request->getPathInfo();
        if (stripos($relativePath, '/category/')) {
            $path = $this->getBaseMediaPathForCategory();
        } elseif (stripos($relativePath, '/product/')) {
            $path = $this->getBaseMediaPathForProduct();
        } else {
            throw new LocalizedException(
                __('Media resource unknown.')
            );
        }

        return trim($path, '/');
    }

    /**
     * @inheritdoc
     */
    public function getMediaPath(string $file): string
    {
        return $this->getBaseMediaPath() . '/' . $this->prepareFile($file);
    }

    /**
     * Process file path.
     *
     * @param string $file
     * @return string
     */
    private function prepareFile(string $file): string
    {
        return ltrim(str_replace('\\', '/', $file), '/');
    }

    /**
     * Get base media path for Product images.
     *
     * @return string
     */
    private function getBaseMediaPathForProduct(): string
    {
        return $this->productMediaConfig->getBaseMediaPath();
    }

    /**
     * Get base media path for Category images.
     *
     * @return string
     */
    private function getBaseMediaPathForCategory(): string
    {
        return FileInfo::ENTITY_MEDIA_PATH;
    }
}
