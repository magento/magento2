<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Entity\Attribute;

use Magento\Framework\DataObject;
use Magento\Eav\Model\Entity\AbstractEntity;

/**
 * Interface for unique attribute validator
 */
interface UniqueValidationInterface
{
    /**
     * Validate if attribute value is unique
     *
     * @param AbstractAttribute $attribute
     * @param DataObject $object
     * @param AbstractEntity $entity
     * @param string $entityLinkField
     * @param array $entityIds
     * @return bool
     */
    public function validate(
        AbstractAttribute $attribute,
        DataObject $object,
        AbstractEntity $entity,
        $entityLinkField,
        array $entityIds
    );
}
