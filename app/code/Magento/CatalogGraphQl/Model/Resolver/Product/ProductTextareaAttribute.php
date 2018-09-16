<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Product;

use Magento\Catalog\Model\Product;
use Magento\CatalogGraphQl\Model\Resolver\Product\ProductTextareaAttribute\FormatFactory;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Resolve rendered content for attributes where HTML content is allowed
 */
class ProductTextareaAttribute implements ResolverInterface
{
    const DEFAULT_CONTENT_FORMAT_IDENTIFIER = 'html';

    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @var FormatFactory
     */
    private $formatFactory;

    /**
     * @param ValueFactory $valueFactory
     * @param FormatFactory $formatFactory
     */
    public function __construct(
        ValueFactory $valueFactory,
        FormatFactory $formatFactory
    ) {
        $this->valueFactory = $valueFactory;
        $this->formatFactory = $formatFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ): Value {
        if (!isset($value['model'])) {
            $result = function () {
                return null;
            };
            return $this->valueFactory->create($result);
        }

        /* @var $product Product */
        $product = $value['model'];
        $fieldName = $field->getName();
        $formatIdentifier = $args['format'] ?? self::DEFAULT_CONTENT_FORMAT_IDENTIFIER;
        $format = $this->formatFactory->create($formatIdentifier);
        $attribute = ['content' => $format->getContent($product, $fieldName)];

        $result = function () use ($attribute) {
            return $attribute;
        };

        return $this->valueFactory->create($result);
    }
}
