<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Nominal tax total
 */
namespace Magento\Tax\Model\Sales\Total\Quote\Nominal;

class Tax extends \Magento\Tax\Model\Sales\Total\Quote\Tax
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
    protected $_itemRowTotalKey = 'tax_amount';

    /**
     * Don't fetch anything
     *
     * @param \Magento\Sales\Model\Quote\Address $address
     * @return array
     */
    public function fetch(\Magento\Sales\Model\Quote\Address $address)
    {
        return \Magento\Sales\Model\Quote\Address\Total\AbstractTotal::fetch($address);
    }

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

    /**
     * Process model configuration array
     *
     * This method can be used for changing totals collect sort order
     *
     * @param array $config
     * @param int|string|\Magento\Store\Model\Store $store
     * @return array
     */
    public function processConfigArray($config, $store)
    {
        /**
         * Nominal totals use sort_order configuration node to define the order (not before or after nodes)
         * If there is a requirement to change the order, in which nominal total is calculated, change sort_order
         */
        return $config;
    }
}
