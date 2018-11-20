<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Entity\Attribute;

/**
 * Class for validate unique attribute value
 */
class UniqueValidator implements UniqueValidationInterface
{
    /**
     * @inheritdoc
     */
    public function validate(AbstractAttribute $attribute, $object, $entityIdField, array $entityIds)
    {
        if (isset($entityIds[0])) {
            return $entityIds[0] == $object->getData($entityIdField);
        }
        return true;
    }
}
