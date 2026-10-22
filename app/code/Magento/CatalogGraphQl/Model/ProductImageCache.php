<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model;

use Magento\Catalog\Model\Config\CatalogMediaConfig;
use Magento\Catalog\Model\Product\Image as ProductImage;
use Magento\Catalog\Model\Product\Image\ParamsBuilder;
use Magento\Catalog\Model\Product\ImageFactory;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Image\Placeholder as PlaceholderProvider;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;

/**
 * Builds product image URLs and ensures the corresponding cache file exists.
 */
class ProductImageCache implements ResetAfterRequestInterface
{
    /**
     * @var string[]
     */
    private array $placeholderCache = [];

    /**
     * @param ImageFactory $productImageFactory
     * @param ParamsBuilder $paramsBuilder
     * @param PlaceholderProvider $placeholderProvider
     * @param CatalogMediaConfig $catalogMediaConfig
     */
    public function __construct(
        private readonly ImageFactory $productImageFactory,
        private readonly ParamsBuilder $paramsBuilder,
        private readonly PlaceholderProvider $placeholderProvider,
        private readonly CatalogMediaConfig $catalogMediaConfig
    ) {
    }

    /**
     * Return product image URL and generate the cache file when missing.
     *
     * @param string $imageType
     * @param string|null $imagePath
     * @return string
     * @throws \Exception
     */
    public function getUrl(string $imageType, ?string $imagePath): string
    {
        if ($this->isEmptyImagePath($imagePath)) {
            return $this->getPlaceholderUrl($imageType);
        }

        $params = $this->paramsBuilder->build(
            [
                'type' => $imageType,
                'width' => null,
                'height' => null,
            ]
        );

        $image = $this->productImageFactory->create();
        $this->configureImage($image, $params, $imageType);
        $image->setBaseFile($imagePath);

        if ($image->isBaseFilePlaceholder()) {
            return $this->getPlaceholderUrl($imageType);
        }

        if ($this->shouldGenerateCacheFile() && !$image->isCached()) {
            $this->generateCacheFile($image, $params);
        }

        return $image->getUrl();
    }

    public function _resetState(): void
    {
        $this->placeholderCache = [];
    }

    /**
     * Apply misc params that affect both processing and the cache path hash.
     *
     * Must run before setBaseFile().
     *
     * @param ProductImage $image
     * @param array $params
     * @param string $imageType
     * @return void
     */
    private function configureImage(ProductImage $image, array $params, string $imageType): void
    {
        $image->setDestinationSubdir($params['image_type'] ?? $imageType);
        $image->setWidth($params['image_width'] ?? null);
        $image->setHeight($params['image_height'] ?? null);
        $image->setKeepAspectRatio($params['keep_aspect_ratio'] ?? true);
        $image->setKeepFrame($params['keep_frame'] ?? true);
        $image->setKeepTransparency($params['keep_transparency'] ?? true);
        $image->setConstrainOnly($params['constrain_only'] ?? true);

        if (isset($params['background']) && is_array($params['background'])) {
            $image->setBackgroundColor($params['background']);
        }

        if (empty($params['watermark_file'])) {
            return;
        }

        $image->setWatermarkFile($params['watermark_file']);

        if (!empty($params['watermark_position'])) {
            $image->setWatermarkPosition($params['watermark_position']);
        }

        if (!empty($params['watermark_image_opacity'])) {
            $image->setWatermarkImageOpacity((int)$params['watermark_image_opacity']);
        }
        if (!empty($params['watermark_width'])) {
            $image->setWatermarkWidth((int)$params['watermark_width']);
        }
        if (!empty($params['watermark_height'])) {
            $image->setWatermarkHeight((int)$params['watermark_height']);
        }
    }

    /**
     * Resize (if needed), apply watermark, and persist the cache file.
     *
     * @param ProductImage $image
     * @param array $params
     * @return void
     */
    private function generateCacheFile(ProductImage $image, array $params): void
    {
        $image->resize();

        if (!empty($params['watermark_file'])) {
            // Watermark properties are already on the model; this applies them via the processor.
            $image->setWatermark($params['watermark_file']);
        }

        $image->saveFile();
    }

    /**
     * Cache files are only written when media URL format is hash-based.
     *
     * @return bool
     */
    private function shouldGenerateCacheFile(): bool
    {
        return $this->catalogMediaConfig->getMediaUrlFormat() === CatalogMediaConfig::HASH;
    }

    /**
     * @param string|null $imagePath
     * @return bool
     */
    private function isEmptyImagePath(?string $imagePath): bool
    {
        return $imagePath === null || $imagePath === '' || $imagePath === 'no_selection';
    }

    /**
     * @param string $imageType
     * @return string
     */
    private function getPlaceholderUrl(string $imageType): string
    {
        if (empty($this->placeholderCache[$imageType])) {
            $this->placeholderCache[$imageType] = $this->placeholderProvider->getPlaceholder($imageType);
        }

        return $this->placeholderCache[$imageType];
    }
}
