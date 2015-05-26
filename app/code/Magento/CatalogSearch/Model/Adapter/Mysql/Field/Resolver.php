<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Adapter\Mysql\Field;

use Magento\Catalog\Model\Resource\Product\Attribute\Collection as AttributeCollection;
use Magento\Framework\Search\Adapter\Mysql\Field\FieldFactory;
use Magento\Framework\Search\Adapter\Mysql\Field\FieldInterface;
use Magento\Framework\Search\Adapter\Mysql\Field\ResolverInterface;

class Resolver implements ResolverInterface
{
    /**
     * @var AttributeCollection
     */
    private $attributeCollection;
    /**
     * @var FieldFactory
     */
    private $fieldFactory;

    /**
     * @param AttributeCollection $attributeCollection
     * @param FieldFactory $fieldFactory
     */
    public function __construct(
        AttributeCollection $attributeCollection,
        FieldFactory $fieldFactory
    ) {
        $this->attributeCollection = $attributeCollection;
        $this->fieldFactory = $fieldFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve($fields)
    {
        $resolvedFields = [];
        foreach ((array)$fields as $field) {
            $attribute = $this->attributeCollection->getItemByColumnValue('attribute_code', $field);
            $id = 0;
            $type = FieldInterface::TYPE_FULLTEXT;
            $fieldName = 'data_index';
            if ($attribute) {
                $id = $attribute->getId();
            }
            $resolvedFields[] = $this->fieldFactory->create(
                [
                    'attributeId' => $id,
                    'field' => $fieldName,
                    'type' => $type
                ]
            );
        }
        return $resolvedFields;
    }
}
