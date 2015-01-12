<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Api\Data;

interface AppliedTaxRateInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const KEY_CODE = 'code';

    const KEY_TITLE = 'title';

    const KEY_PERCENT = 'percent';
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
}
