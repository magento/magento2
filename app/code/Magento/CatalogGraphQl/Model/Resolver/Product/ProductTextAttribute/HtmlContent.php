<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Product\ProductTextAttribute;

use Magento\Catalog\Model\Product;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Helper\Output as OutputHelper;

/**
 * HTML content of Product Text Attribute
 */
class HtmlContent implements ResolverInterface
{
    /**
     * @var OutputHelper
     */
    private $outputHelper;

    /**
     * @param OutputHelper $outputHelper
     */
    public function __construct(
        OutputHelper $outputHelper
    ) {
        $this->outputHelper = $outputHelper;
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
            return [];
        }

        /* @var $product Product */
        $product = $value['model'];
        $fieldName = $field->getName();
        $renderedValue = $this->outputHelper->productAttribute($product, $product->getData($fieldName), $fieldName);

        return $renderedValue;
    }
}
