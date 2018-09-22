<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Product;

use Magento\Catalog\Model\Product;
use Magento\CatalogGraphQl\Model\Resolver\Product\ProductTextAttribute\FormatList;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Resolve rendered content for attributes where HTML content is allowed
 */
class ProductTextAttribute implements ResolverInterface
{
    /**
     * @var FormatList
     */
    private $formatList;

    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @var string
     */
    private $defaultFormat = 'html';

    /**
     * @param ValueFactory $valueFactory
     * @param FormatList $formatFactory
     */
    public function __construct(
        ValueFactory $valueFactory,
        FormatList $formatFactory
    ) {
        $this->valueFactory = $valueFactory;
        $this->formatList = $formatFactory;
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
            $result = [];
            return $this->valueFactory->create($result);
        }

        /* @var $product Product */
        $product = $value['model'];
        $fieldName = $field->getName();
        $formatIdentifier = $args['format'] ?? $this->defaultFormat;
        $format = $this->formatList->create($formatIdentifier);
        $result = ['content' => $format->getContent($product, $fieldName)];

        return $this->valueFactory->create($result);
    }
}
