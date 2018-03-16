<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogGraphQl\Model\Resolver\Products\FilterArgument;

use Magento\Framework\GraphQl\Argument\Filter\FilterArgumentValueFactory;
use Magento\Framework\GraphQl\Argument\ValueParserInterface;

/**
 * Parses a mixed value to a FindArgumentValue
 */
class ValueParser implements ValueParserInterface
{
    /** @var AstConverter */
    private $clauseConverter;

    /** @var FilterArgumentValueFactory */
    private $filterArgumentValueFactory;

    /**
     * @param AstConverter $clauseConverter
     * @param FilterArgumentValueFactory $filterArgumentValueFactory
     */
    public function __construct(
        AstConverter $clauseConverter,
        FilterArgumentValueFactory $filterArgumentValueFactory
    ) {
        $this->clauseConverter = $clauseConverter;
        $this->filterArgumentValueFactory = $filterArgumentValueFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function parse($value)
    {
        $filters = $this->clauseConverter->getFilterFromAst(\Magento\Catalog\Model\Product::ENTITY, $value);
        return $this->filterArgumentValueFactory->create($filters);
    }
}
