<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Product;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Helper\Output as OutputHelper;

/**
 * Resolve rendered content for attributes where HTML content is allowed
 */
class ProductComplexTextAttribute implements ResolverInterface
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
        ?array $value = null,
        ?array $args = null
    ): array {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        /* @var $product Product */
        $product = $value['model'];
        $fieldName = $field->getName();
        $renderedValue = $this->outputHelper->productAttribute($product, $product->getData($fieldName), $fieldName);

        return ['html' => $renderedValue ?? ''];
    }
}
