<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SwatchesGraphQl\Model\Resolver\Product\Options\DataProvider;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Image\UrlBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\GraphQl\Query\EnumLookup;
use Magento\Swatches\Helper\Data as SwatchData;
use Magento\Swatches\Helper\Media as SwatchesMedia;
use Magento\Swatches\Model\Swatch;

/**
 * Swatch data provider
 */
class SwatchDataProvider
{
    /**
     * @var SwatchData
     */
    private $swatchHelper;

    /**
     * @var SwatchesMedia
     */
    private $swatchMediaHelper;

    /**
     * @var UrlBuilder
     */
    private $imageUrlBuilder;

    /**
     * @var EnumLookup
     */
    private $enumLookup;

    /**
     * SwatchDataProvider constructor.
     *
     * @param SwatchData $swatchHelper
     * @param SwatchesMedia $swatchMediaHelper
     * @param UrlBuilder $imageUrlBuilder
     * @param EnumLookup $enumLookup
     */
    public function __construct(
        SwatchData $swatchHelper,
        SwatchesMedia $swatchMediaHelper,
        UrlBuilder $imageUrlBuilder,
        EnumLookup $enumLookup
    ) {
        $this->swatchHelper = $swatchHelper;
        $this->swatchMediaHelper = $swatchMediaHelper;
        $this->imageUrlBuilder = $imageUrlBuilder;
        $this->enumLookup = $enumLookup;
    }

    /**
     * Get swatch data
     *
     * @param string $optionId
     * @param ProductInterface $product
     *
     * @return array
     *
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws \LogicException
     */
    public function getData(string $optionId, ProductInterface $product): array
    {
        $swatches = $this->swatchHelper->getSwatchesByOptionsId([$optionId]);
        if (!isset($swatches[$optionId], $swatches[$optionId]['type'], $swatches[$optionId]['value'])) {
            return null;
        }

        $type = (int)$swatches[$optionId]['type'];
        $value = $swatches[$optionId]['value'];
        $thumbnail = null;

        // change value & thumbnail if type is 'visual'
        if ($type === Swatch::SWATCH_TYPE_VISUAL_IMAGE) {
            $thumbnail = $this->swatchMediaHelper->getSwatchAttributeImage(Swatch::SWATCH_THUMBNAIL_NAME, $value);
            $value = $this->swatchMediaHelper->getSwatchAttributeImage(Swatch::SWATCH_IMAGE_NAME, $value);
        }

        $attributeData = $this->getSwatchAttributeDataByOptionId($product, $optionId);
        // check if swatch value should be getting from related product image
        if (!$this->isUseProductImageForSwatch($attributeData)) {
            return $this->getResultArray($value, $type, $thumbnail);
        }

        // get product with existing image
        $variationProduct = $this->getVariationProduct($attributeData, $optionId, $product);
        if (null === $variationProduct) {
            return $this->getResultArray($value, $type, $thumbnail);
        }

        // set 'visual' type, because the product image is using as swatch value
        $type = Swatch::SWATCH_TYPE_VISUAL_IMAGE;

        // get image from child product
        $productImage = $this->getSwatchProductImage($variationProduct, Swatch::SWATCH_IMAGE_NAME);
        if (null !== $productImage) {
            $value = $productImage;
        }

        // get thumbnail from child product
        $productThumbnail = $this->getSwatchProductImage($variationProduct, Swatch::SWATCH_THUMBNAIL_NAME);
        if (null !== $productThumbnail) {
            $thumbnail = $productThumbnail;
        }

        return $this->getResultArray($value, $type, $thumbnail);
    }

    /**
     * Get result array
     *
     * @param string $value
     * @param int $type
     * @param null|string $thumbnail
     *
     * @return array
     *
     * @throws RuntimeException
     */
    private function getResultArray(string $value, int $type, ?string $thumbnail)
    {
        return [
            'value' => $value,
            'type' => $this->enumLookup->getEnumValueFromField('SwatchTypeEnum', (string)$type),
            'thumbnail' => $thumbnail
        ];
    }

    /**
     * Is swatch images should be getting from related simple products
     *
     * @param array $attributeData
     *
     * @return bool
     */
    private function isUseProductImageForSwatch(array $attributeData) : bool
    {
        return isset($attributeData['use_product_image_for_swatch']) && $attributeData['use_product_image_for_swatch'];
    }

    /**
     * Get simple product with first variation swatch image or image
     *
     * @param array $attributeData
     * @param string $optionId
     * @param ProductInterface $product
     *
     * @return ProductInterface|null
     */
    private function getVariationProduct(array $attributeData, string $optionId, ProductInterface $product) : ?ProductInterface
    {
        $attributeCode = $attributeData['attribute_code'];
        $requiredAttributes = [
            $attributeCode => $optionId
        ];

        $variationProduct = $this->swatchHelper->loadFirstVariationWithSwatchImage($product, $requiredAttributes);
        if ($variationProduct instanceof ProductInterface) {
            return $variationProduct;
        }

        $variationProduct = $this->swatchHelper->loadFirstVariationWithImage($product, $requiredAttributes);
        if ($variationProduct instanceof ProductInterface) {
            return $variationProduct;
        }

        return null;
    }

    /**
     * Get swatch product image
     *
     * @param ProductInterface $product
     * @param string $imageType
     *
     * @return string|null
     */
    private function getSwatchProductImage(ProductInterface $product, $imageType) : ?string
    {
        if ($this->isProductHasImage($product, Swatch::SWATCH_IMAGE_NAME)) {
            $swatchImageId = $imageType;
            $imageAttributes = ['type' => Swatch::SWATCH_IMAGE_NAME];
        } elseif ($this->isProductHasImage($product, 'image')) {
            $swatchImageId = $imageType == Swatch::SWATCH_IMAGE_NAME ? 'swatch_image_base' : 'swatch_thumb_base';
            $imageAttributes = ['type' => 'image'];
        }

        if (empty($swatchImageId) || empty($imageAttributes['type'])) {
            return null;
        }

        return $this->imageUrlBuilder->getUrl($product->getData($imageAttributes['type']), $swatchImageId);
    }

    /**
     * Is product has image
     *
     * @param ProductInterface $product
     * @param string $imageType
     *
     * @return bool
     */
    private function isProductHasImage(ProductInterface $product, string $imageType) : bool
    {
        return $product->getData($imageType) !== null && $product->getData($imageType) != SwatchData::EMPTY_IMAGE_VALUE;
    }

    /**
     * Get swatch attribute data by option id
     *
     * @param ProductInterface $product
     * @param string $optionId
     *
     * @return array
     *
     * @throws LocalizedException
     * @throws \LogicException
     * @throws NoSuchEntityException
     */
    private function getSwatchAttributeDataByOptionId(ProductInterface $product, string $optionId) : array
    {
        $attributesData = $this->swatchHelper->getSwatchAttributesAsArray($product);
        foreach ($attributesData as $attributeData) {
            if (!isset($attributeData['options']) || !is_array($attributeData['options'])) {
                continue;
            }

            if (array_key_exists($optionId, $attributeData['options'])) {
                return $attributeData;
            }
        }

        throw new LocalizedException(__(sprintf('Cannot find the attribute with option id "%1".', $optionId)));
    }
}
