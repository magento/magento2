<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Plugin;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeCollectionFactory;
use Magento\Framework\GraphQl\Query\Fields;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\Quote\Model\Quote\Config as QuoteConfig;

/**
 * Class for extending product attributes for quote.
 */
class ProductAttributesExtender implements ResetAfterRequestInterface
{
    /**
     * @var array
     */
    private $attributes;

    /**
     * @param Fields $fields
     * @param AttributeCollectionFactory $attributeCollectionFactory
     */
    public function __construct(
        private Fields $fields,
        private AttributeCollectionFactory $attributeCollectionFactory,
    ) {
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
        if (!$this->attributes) {
            $attributeCollection = $this->attributeCollectionFactory->create()
                ->removeAllFieldsFromSelect()
                ->addFieldToSelect('attribute_code')
                ->setCodeFilter($this->fields->getFieldsUsedInQuery())
                ->load();
            $this->attributes = $attributeCollection->getColumnValues('attribute_code');
        }
        return array_unique(array_merge($result, $this->attributes));
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->attributes = [];
    }
}
