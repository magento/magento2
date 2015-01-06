<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Weee\Model\Total\Quote\Nominal;

/**
 * Nominal fixed product tax total
 */
class Weee extends \Magento\Weee\Model\Total\Quote\Weee
{
    /**
     * Don't add amounts to address
     *
     * @var bool
     */
    protected $_canAddAmountToAddress = false;

    /**
     * Custom row total key
     *
     * @var string
     */
    protected $_itemRowTotalKey = 'weee_tax_applied_row_amount';

    /**
     * Get nominal items only
     *
     * @param \Magento\Sales\Model\Quote\Address $address
     * @return array
     */
    protected function _getAddressItems(\Magento\Sales\Model\Quote\Address $address)
    {
        return $address->getAllNominalItems();
    }
}
