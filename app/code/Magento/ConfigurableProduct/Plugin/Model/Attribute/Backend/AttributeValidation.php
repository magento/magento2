<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Plugin\Model\Attribute\Backend;

use Closure;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
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
     * @param array|null $unskippableAttributes
     */
    public function __construct(
        Configurable $configurableProductType,
        array $unskippableAttributes = []
    ) {
        $this->configurableProductType = $configurableProductType;
        $this->unskippableAttributes = $unskippableAttributes;
    }
    /**
     * Verify is attribute used for configurable product creation and should not be validated.
     *
     * @param AbstractBackend $subject
     * @param \Closure $proceed
     * @param DataObject $entity
     * @return bool
     */
    public function aroundValidate(AbstractBackend $subject, Closure $proceed, DataObject $entity)
    {
        $attribute = $subject->getAttribute();
        if ($this->isAttributeShouldNotBeValidated($entity, $attribute)
            && !in_array($attribute->getAttributeCode(), $this->unskippableAttributes)
        ) {
            return true;
        }
        return $proceed($entity);
    }
    /**
     * Verify if attribute is a part of configurable product and should not be validated.
     *
     * @param DataObject $entity
     * @param AbstractAttribute $attribute
     * @return bool
     */
    private function isAttributeShouldNotBeValidated(DataObject $entity, AbstractAttribute $attribute): bool
    {
        if (!($entity instanceof ProductInterface && $entity->getTypeId() === Configurable::TYPE_CODE)) {
            return false;
        }
        $attributeId = $attribute->getAttributeId();
        $options = $entity->getConfigurableProductOptions() ?: [];
        $configurableAttributeIds = array_column($options, 'attribute_id');

        return in_array($attributeId, $configurableAttributeIds)
            || in_array($attributeId, $this->configurableProductType->getUsedProductAttributeIds($entity), true);
    }
}
