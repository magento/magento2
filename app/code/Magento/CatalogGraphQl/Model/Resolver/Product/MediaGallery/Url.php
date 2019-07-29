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
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Model\View\Asset\ImageFactory as AssetImageFactory;

/**
 * Returns media url
 */
class Url implements ResolverInterface
{
    /**
     * @var ImageFactory
     */
    private $productImageFactory;
    /**
     * @var PlaceholderProvider
     */
    private $placeholderProvider;

    private $assetImageFactory;

    /**
     * @param ImageFactory $productImageFactory
     * @param PlaceholderProvider $placeholderProvider
     * @param AssetImageFactory $assetImageFactory
     */
    public function __construct(
        ImageFactory $productImageFactory,
        PlaceholderProvider $placeholderProvider,
        AssetImageFactory $assetImageFactory
    ) {
        $this->productImageFactory = $productImageFactory;
        $this->placeholderProvider = $placeholderProvider;
        $this->assetImageFactory = $assetImageFactory;
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
            return $this->getImageUrl($value['image_type'], $imagePath);
        }
        if (isset($value['file'])) {
            $asset = $this->assetImageFactory->create(
                [
                    'miscParams' => [],
                    'filePath' => $value['file']
                ]
            );
            return $asset->getUrl();
        }
        return [];
    }

    /**
     * Get image URL
     *
     * @param string $imageType
     * @param string|null $imagePath
     * @return string
     * @throws \Exception
     */
    private function getImageUrl(string $imageType, ?string $imagePath): string
    {
        $image = $this->productImageFactory->create();
        $image->setDestinationSubdir($imageType)
            ->setBaseFile($imagePath);

        if ($image->isBaseFilePlaceholder()) {
            return $this->placeholderProvider->getPlaceholder($imageType);
        }

        return $image->getUrl();
    }
}
