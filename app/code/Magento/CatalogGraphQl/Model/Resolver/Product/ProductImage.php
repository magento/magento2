<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Product;

use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Returns product's image data
 */
class ProductImage implements ResolverInterface
{
    /** @var array */
    private static $catalogImageLabelTypes = [
        'image' => 'image_label',
        'small_image' => 'small_image_label',
        'thumbnail' => 'thumbnail_label'
    ];

    /** @var array */
    private $imageTypeLabels;

    /**
     * @param array $imageTypeLabels
     */
    public function __construct(
        array $imageTypeLabels = []
    ) {
        $this->imageTypeLabels =  array_replace(self::$catalogImageLabelTypes, $imageTypeLabels);
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
    ): array {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        /** @var Product $product */
        $product = $value['model'];
        $label =  $value['name'] ?? null;
        if (isset($this->imageTypeLabels[$info->fieldName])
            && !empty($value[$this->imageTypeLabels[$info->fieldName]])) {
            $label = $value[$this->imageTypeLabels[$info->fieldName]];
        }

        return [
            'model' => $product,
            'image_type' => $field->getName(),
            'label' => $label
        ];
    }
}
