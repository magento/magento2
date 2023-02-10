<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Model;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Exception\LocalizedException;

/**
 * Composite Reserved Attribute Checker
 *
 * Iterates through individual Reserved Attribute Checkers to check whether attribute is reserved by system
 */
class ReservedAttributeChecker implements ReservedAttributeCheckerInterface
{
    /**
     * @var ReservedAttributeCheckerInterface[]
     */
    private $validators;

    /**
     * @param array $validators
     */
    public function __construct(
        array $validators = []
    ) {
        $this->validators = $validators;
    }

    /**
     * @inheritdoc
     */
    public function isReservedAttribute(AbstractAttribute $attribute): bool
    {
        $isReserved = false;
        $entityTypeCode = $this->getAttributeEntityTypeCode($attribute);
        $validators = $this->validators[$entityTypeCode] ?? [];
        foreach ($validators as $validator) {
            $isReserved = $validator->isReservedAttribute($attribute);
            if ($isReserved === true) {
                break;
            }
        }

        return $isReserved;
    }

    /**
     * Returns attribute entity type code.
     *
     * @param AbstractAttribute $attribute
     * @return string|null
     */
    private function getAttributeEntityTypeCode(AbstractAttribute $attribute): ?string
    {
        try {
            $result = $attribute->getEntityType()->getEntityTypeCode();
        } catch (LocalizedException $e) {
            $result = null;
        }

        return $result;
    }
}
