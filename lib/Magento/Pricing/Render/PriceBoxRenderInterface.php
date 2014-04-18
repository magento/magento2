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
 * @category    Magento
 * @package     Magento_Pricing
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Pricing\Render;

use Magento\Pricing\Amount\AmountInterface;
use Magento\Pricing\Price\PriceInterface;
use Magento\Pricing\Object\SaleableInterface;

/**
 * Price box render interface
 */
interface PriceBoxRenderInterface
{
    /**
     * @return SaleableInterface
     */
    public function getSaleableItem();

    /**
     * Retrieve price object
     * (to use in templates only)
     *
     * @return PriceInterface
     */
    public function getPrice();

    /**
     * Retrieve amount html for given price and arguments
     * (to use in templates only)
     *
     * @param AmountInterface $price
     * @param array $arguments
     * @return string
     */
    public function renderAmount(AmountInterface $price, array $arguments = []);
}
