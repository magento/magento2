<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Entity\Attribute;

/**
 * Interface for unique attribute validator
 */
interface UniqueValidationInterface
{
    /**
     * Validate if attribute value is unique
     *
     * @param AbstractAttribute $attribute
     * @param \Magento\Framework\DataObject $object
     * @param string $entityIdField
     * @param array $entityIds
     * @return bool
     */
    public function validate(AbstractAttribute $attribute, $object, $entityIdField, array $entityIds);
}
