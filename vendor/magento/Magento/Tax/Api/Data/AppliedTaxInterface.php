<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Tax\Api\Data;

interface AppliedTaxInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const KEY_TAX_RATE_KEY = 'tax_rate_key';

    const KEY_PERCENT = 'percent';

    const KEY_AMOUNT = 'amount';

    const KEY_RATES = 'rates';
    /**#@-*/

    /**
     * Get tax rate key
     *
     * @return string|null
     */
    public function getTaxRateKey();

    /**
     * Get percent
     *
     * @return float
     */
    public function getPercent();

    /**
     * Get amount
     *
     * @return float
     */
    public function getAmount();

    /**
     * Get rates
     *
     * @return \Magento\Tax\Api\Data\AppliedTaxRateInterface[]|null
     */
    public function getRates();
}
