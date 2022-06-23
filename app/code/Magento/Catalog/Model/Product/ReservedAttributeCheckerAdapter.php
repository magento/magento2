<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\ReservedAttributeCheckerInterface;

/**
 * Adapter for \Magento\Catalog\Model\Product\ReservedAttributeList
 *
 * Is created to implement proper interface and to use api class ReservedAttributeList
 * while keeping it backward compatible
 * @see \Magento\Catalog\Model\Product\ReservedAttributeList
 */
class ReservedAttributeCheckerAdapter implements ReservedAttributeCheckerInterface
{
    /**
     * @var ReservedAttributeList
     */
    private $reservedAttributeList;

    /**
     * @param ReservedAttributeList $reservedAttributeList
     */
    public function __construct(
        ReservedAttributeList $reservedAttributeList
    ) {
        $this->reservedAttributeList = $reservedAttributeList;
    }

    /**
     * @inheritdoc
     */
    public function isReservedAttribute(AbstractAttribute $attribute): bool
    {
        return $this->reservedAttributeList->isReservedAttribute($attribute);
    }
}
