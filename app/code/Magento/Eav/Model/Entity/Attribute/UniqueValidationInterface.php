<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Eav\Model\Entity\Attribute;

use Magento\Framework\DataObject;
use Magento\Eav\Model\Entity\AbstractEntity;

/**
 * Interface for unique attribute validator
 *
 * @api
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
