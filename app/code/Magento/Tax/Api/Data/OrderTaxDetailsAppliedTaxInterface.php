<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Api\Data;

interface OrderTaxDetailsAppliedTaxInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const KEY_CODE = 'code';

    const KEY_TITLE = 'title';

    const KEY_PERCENT = 'percent';

    const KEY_AMOUNT = 'amount';

    const KEY_BASE_AMOUNT = 'base_amount';
    /**#@-*/

    /**
     * Get code
     *
     * @return string|null
     */
    public function getCode();

    /**
     * Get Title
     *
     * @return string|null
     */
    public function getTitle();

    /**
     * Get Tax Percent
     *
     * @return float|null
     */
    public function getPercent();

    /**
     * Get tax amount
     *
     * @return float
     */
    public function getAmount();

    /**
     * Get tax amount in base currency
     *
     * @return float
     */
    public function getBaseAmount();
}
