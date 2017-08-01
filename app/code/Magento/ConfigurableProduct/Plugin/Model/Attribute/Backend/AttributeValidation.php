<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Plugin\Model\Attribute\Backend;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

/**
 * Skip validate attributes used for create configurable product
 * @since 2.2.0
 */
class AttributeValidation
{
    /**
     * @var Configurable
     * @since 2.2.0
     */
    private $configurableProductType;

    /**
     * AttributeValidation constructor.
     * @param Configurable $configurableProductType
     * @since 2.2.0
     */
    public function __construct(
        Configurable $configurableProductType
    ) {
        $this->configurableProductType = $configurableProductType;
    }

    /**
     * @param \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\DataObject $entity
     * @return bool
     * @since 2.2.0
     */
    public function aroundValidate(
        \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend $subject,
        \Closure $proceed,
        \Magento\Framework\DataObject $entity
    ) {
        $attribute = $subject->getAttribute();
        if ($entity instanceof ProductInterface
            && $entity->getTypeId() == Configurable::TYPE_CODE
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
