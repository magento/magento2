<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Product\MediaGallery;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\ImageFactory;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Image\Placeholder as PlaceholderProvider;
use Magento\CatalogGraphQl\Model\CheckImageCacheFileExist;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Returns media url
 */
class Url implements ResolverInterface
{
    /**
     * @var CheckImageCacheFileExist
     */
    private $checkImageCacheFileExist;

    /**
     * @var string[]
     */
    private $placeholderCache = [];

    /**
     * @var PlaceholderProvider
     */
    private $placeholderProvider;

    /**
     * @var ImageFactory
     */
    private $productImageFactory;

    /**
     * @param ImageFactory $productImageFactory
     * @param PlaceholderProvider $placeholderProvider
     */
    public function __construct(
        CheckImageCacheFileExist $checkImageCacheFileExist,
        ImageFactory $productImageFactory,
        PlaceholderProvider $placeholderProvider
    ) {
        $this->checkImageCacheFileExist = $checkImageCacheFileExist;
        $this->productImageFactory = $productImageFactory;
        $this->placeholderProvider = $placeholderProvider;
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
        if (!isset($value['image_type']) && !isset($value['file'])) {
            throw new LocalizedException(__('"image_type" value should be specified'));
        }

        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        /** @var Product $product */
        $product = $value['model'];
        if (isset($value['image_type'])) {
            $imagePath = $product->getData($value['image_type']);
            $imgUrl = $this->getImageUrl($value['image_type'], $imagePath);
            if ($imagePath) {
                return $this->checkImageCacheFileExist->execute(
                    $imgUrl,
                    $imagePath,
                    $value['image_type']
                );
            } else {

                return $imgUrl;
            }

        } elseif (isset($value['file'])) {
            $imgUrl = $this->getImageUrl('image', $value['file']);
            return $this->checkImageCacheFileExist->execute(
                $imgUrl,
                $value['file'],
                'image'
            );
        }

        return [];
    }

    /**
     * Get image URL
     *
     * @param string $imageType
     * @param string|null $imagePath
     *
     * @return string
     * @throws \Exception
     */
    private function getImageUrl(string $imageType, ?string $imagePath): string
    {
        if (empty($imagePath) && !empty($this->placeholderCache[$imageType])) {
            return $this->placeholderCache[$imageType];
        }
        $image = $this->productImageFactory->create();
        $image->setDestinationSubdir($imageType)
            ->setBaseFile($imagePath);

        if ($image->isBaseFilePlaceholder()) {
            $this->placeholderCache[$imageType] = $this->placeholderProvider->getPlaceholder($imageType);
            return $this->placeholderCache[$imageType];
        }

        return $image->getUrl();
    }
}
