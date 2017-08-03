<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Api;

/**
 * Interface for managing classes rates.
 * @api
 * @since 2.0.0
 */
interface TaxClassManagementInterface
{
    /**#@+
     * Tax class type.
     */
    const TYPE_CUSTOMER = 'CUSTOMER';
    const TYPE_PRODUCT = 'PRODUCT';
    /**#@-*/

    /**
     * Get tax class id
     *
     * @param \Magento\Tax\Api\Data\TaxClassKeyInterface|null $taxClassKey
     * @param string $taxClassType
     * @return int|null
     * @since 2.0.0
     */
    public function getTaxClassId($taxClassKey, $taxClassType = self::TYPE_PRODUCT);
}
