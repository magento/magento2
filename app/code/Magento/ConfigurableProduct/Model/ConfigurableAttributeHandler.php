<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Model;

use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Zend_Db_Expr;

class ConfigurableAttributeHandler
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param CollectionFactory $attributeColFactory
     */
    public function __construct(
        CollectionFactory $attributeColFactory
    ) {
        $this->collectionFactory = $attributeColFactory;
    }

    /**
     * Retrieve list of attributes applicable for configurable product
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection
     */
    public function getApplicableAttributes()
    {
        /** @var $collection \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(
            'frontend_input',
            'select'
        )->addFieldToFilter(
            'is_user_defined',
            1
        )->addFieldToFilter(
            'is_global',
            ScopedAttributeInterface::SCOPE_GLOBAL
        );

        $types = [
            Type::TYPE_SIMPLE,
            Type::TYPE_VIRTUAL,
            Configurable::TYPE_CODE,
        ];
        $applyToArr = [];
        foreach ($types as $type) {
            $applyToArr[] = "apply_to REGEXP '(^|(.*,))$type($|,.*)'";
        }
        $whereExprStr = 'apply_to IS NULL OR (' . implode(' AND ', $applyToArr) . ')';

        $collection->getSelect()->where(new Zend_Db_Expr($whereExprStr));

        return $collection;
    }

    /**
     * Check if attribute is applicable for configurable products
     * @deprecated is applicable check is added to collection query
     * @see \Magento\ConfigurableProduct\Model\ConfigurableAttributeHandler::getApplicableAttributes
     * @param \Magento\Catalog\Api\Data\ProductAttributeInterface $attribute
     *
     * @return bool
     */
    public function isAttributeApplicable($attribute)
    {
        $types = [
            Type::TYPE_SIMPLE,
            Type::TYPE_VIRTUAL,
            Configurable::TYPE_CODE,
        ];
        return !$attribute->getApplyTo() || count(array_diff($types, $attribute->getApplyTo())) === 0;
    }
}
