<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Model\Entity\Attribute;

use Magento\Framework\DataObject;
use Magento\Eav\Model\Entity\AbstractEntity;

/**
 * Class for validate unique attribute value.
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
        string $entityLinkField,
        array $entityIds
    ): bool {
        if (isset($entityIds[0])) {
            return $entityIds[0] == $object->getData($entityLinkField);
        }

        return true;
    }
}
