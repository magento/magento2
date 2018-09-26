<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Category;

use Magento\Catalog\Model\Category;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Helper\Output as OutputHelper;

/**
 * Resolve rendered content for category attributes where HTML content is allowed
 */
class CategoryHtmlAttribute implements ResolverInterface
{
    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @var OutputHelper
     */
    private $outputHelper;

    /**
     * @param ValueFactory $valueFactory
     * @param OutputHelper $outputHelper
     */
    public function __construct(
        ValueFactory $valueFactory,
        OutputHelper $outputHelper
    ) {
        $this->valueFactory = $valueFactory;
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
    ): Value {
        if (!isset($value['model'])) {
            $result = function () {
                return null;
            };
            return $this->valueFactory->create($result);
        }

        /* @var $category Category */
        $category = $value['model'];
        $fieldName = $field->getName();
        $renderedValue = $this->outputHelper->categoryAttribute($category, $category->getData($fieldName), $fieldName);

        $result = function () use ($renderedValue) {
            return $renderedValue;
        };

        return $this->valueFactory->create($result);
    }
}
