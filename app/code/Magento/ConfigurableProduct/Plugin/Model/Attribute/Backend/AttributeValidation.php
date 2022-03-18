<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Plugin\Model\Attribute\Backend;

use Closure;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Framework\DataObject;

/**
 * Skip validate attributes used for create configurable product.
 */
class AttributeValidation
{
    /**
     * @var Configurable
     */
    private $configurableProductType;

    /**
     * @var array
     */
    private $unskippableAttributes;

    /**
     * @param Configurable $configurableProductType
     * @param array $unskippableAttributes
     */
    public function __construct(
        Configurable $configurableProductType,
        array $unskippableAttributes = []
    ) {
        $this->configurableProductType = $configurableProductType;
        $this->unskippableAttributes = $unskippableAttributes;
    }

    /**
     * Around plugin to skip attribute validation used for create configurable product.
     *
     * @param AbstractBackend $subject
     * @param \Closure $proceed
     * @param DataObject $entity
     * @return bool
     */
    public function aroundValidate(AbstractBackend $subject, Closure $proceed, DataObject $entity)
    {
        $attribute = $subject->getAttribute();
        if ($entity instanceof ProductInterface
            && $entity->getTypeId() == Configurable::TYPE_CODE
            && !in_array($attribute->getAttributeCode(), $this->unskippableAttributes)
            && in_array(
                $attribute->getAttributeId(),
                $this->configurableProductType->getUsedProductAttributeIds($entity),
                true
            )
        ) {
            return true;
        }

        return $proceed($entity);
    }
}
