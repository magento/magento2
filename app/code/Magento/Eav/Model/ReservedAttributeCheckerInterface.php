<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Model;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;

/**
 * Checks whether attribute is reserved by system
 */
interface ReservedAttributeCheckerInterface
{
    /**
     * Check whether attribute is reserved by system.
     *
     * Check that given user defined EAV attribute doesn't contain the attribute code
     * that matches to a getter field related to related model (e.g. product, category, customer...)
     *
     * @param AbstractAttribute $attribute
     * @return bool
     */
    public function isReservedAttribute(AbstractAttribute $attribute): bool;
}
