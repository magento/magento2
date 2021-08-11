<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Plugin;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeCollectionFactory;
use Magento\Framework\GraphQl\Query\Fields;
use Magento\Quote\Model\Quote\Config as QuoteConfig;

/**
 * Class for extending product attributes for quote.
 */
class ProductAttributesExtender
{
    /**
     * @var Fields
     */
    private $fields;

    /**
     * @var AttributeCollectionFactory
     */
    private $attributeCollectionFactory;

    /**
     * @param Fields $fields
     * @param AttributeCollectionFactory $attributeCollectionFactory
     */
    public function __construct(
        Fields $fields,
        AttributeCollectionFactory $attributeCollectionFactory
    ) {
        $this->fields = $fields;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
    }

    /**
     * Add requested product attributes.
     *
     * @param QuoteConfig $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetProductAttributes(QuoteConfig $subject, array $result): array
    {
        $attributeCollection = $this->attributeCollectionFactory->create()
            ->removeAllFieldsFromSelect()
            ->addFieldToSelect('attribute_code')
            ->setCodeFilter($this->fields->getFieldsUsedInQuery())
            ->load();
        $attributes = $attributeCollection->getColumnValues('attribute_code');

        return array_unique(array_merge($result, $attributes));
    }
}
