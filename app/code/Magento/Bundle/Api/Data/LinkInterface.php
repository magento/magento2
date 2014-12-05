<?php
/**
 *
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

namespace Magento\Bundle\Api\Data;

interface LinkInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Get linked product sku
     *
     * @return string|null
     */
    public function getSku();

    /**
     * Get option id
     *
     * @return int|null
     */
    public function getOptionId();

    /**
     * Get qty
     *
     * @return float|null
     */
    public function getQty();

    /**
     * Get position
     *
     * @return int|null
     */
    public function getPosition();

    /**
     * Get is defined
     *
     * @return bool|null
     */
    public function getIsDefined();

    /**
     * Get is default
     *
     * @return bool
     */
    public function getIsDefault();

    /**
     * Get price
     *
     * @return float
     */
    public function getPrice();

    /**
     * Get price type
     *
     * @return int
     */
    public function getPriceType();

    /**
     * Get whether quantity could be changed
     *
     * @return int|null
     */
    public function getCanChangeQuantity();
}
