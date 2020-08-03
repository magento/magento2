<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\SearchAdapter\Filter\Builder;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeProvider;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Search\Request\Filter\Term as TermFilterRequest;
use Magento\Framework\Search\Request\FilterInterface as RequestFilterInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType\ConverterInterface
    as FieldTypeConverterInterface;

/**
 * Term filter builder
 */
class Term implements FilterInterface
{
    /**
     * @var FieldMapperInterface
     */
    protected $fieldMapper;

    /**
     * @var AttributeProvider
     */
    private $attributeAdapterProvider;

    /**
     * @var array
     * @see \Magento\Elasticsearch\Elasticsearch5\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType\Resolver\IntegerType::$integerTypeAttributes
     */
    private $integerTypeAttributes = ['category_ids'];

    /**
     * @param FieldMapperInterface $fieldMapper
     * @param AttributeProvider $attributeAdapterProvider
     * @param array $integerTypeAttributes
     */
    public function __construct(
        FieldMapperInterface $fieldMapper,
        AttributeProvider $attributeAdapterProvider = null,
        array $integerTypeAttributes = []
    ) {
        $this->fieldMapper = $fieldMapper;
        $this->attributeAdapterProvider = $attributeAdapterProvider
            ?? ObjectManager::getInstance()->get(AttributeProvider::class);
        $this->integerTypeAttributes = array_merge($this->integerTypeAttributes, $integerTypeAttributes);
    }

    /**
     * Build term filter request
     *
     * @param RequestFilterInterface|TermFilterRequest $filter
     * @return array
     */
    public function buildFilter(RequestFilterInterface $filter)
    {
        $filterQuery = [];

        $attribute = $this->attributeAdapterProvider->getByAttributeCode($filter->getField());
        $fieldName = $this->fieldMapper->getFieldName($filter->getField());

        if ($attribute->isTextType() && !in_array($attribute->getAttributeCode(), $this->integerTypeAttributes)) {
            $suffix = FieldTypeConverterInterface::INTERNAL_DATA_TYPE_KEYWORD;
            $fieldName .= '.' . $suffix;
        }

        if ($filter->getValue() !== false) {
            $operator = is_array($filter->getValue()) ? 'terms' : 'term';
            $filterQuery []= [
                $operator => [
                    $fieldName => $filter->getValue(),
                ],
            ];
        }
        return $filterQuery;
    }
}
