<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Eav\Model\Entity\Attribute;

use Magento\Framework\DataObject;
use Magento\Eav\Model\Entity\AbstractEntity;

/**
 * Class for validate unique attribute value
 */
class UniqueValidator implements UniqueValidationInterface
{
    /**
     * @inheritdoc
     */
    public function validate(
        AbstractAttribute $attribute,
        DataObject $object,
        AbstractEntity $entity,
        $entityLinkField,
        array $entityIds
    ) {
        if ($entityIds) {
            // check for current and future updates
            return in_array($object->getData($entityLinkField), $entityIds);
        }
        return true;
    }
}
