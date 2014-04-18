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

namespace Magento\Pricing\Object;

/**
 * //@TODO Templates invoke methods that are not defined in the interface:
 *  getProductUrl():
 *      /app\code\Magento\Catalog\view\frontend\product\price\final_price.phtml
 *      /app\code\Magento\Catalog\view\frontend\product\price\msrp_item.phtml
 *
 *  getId() - /app\code\Magento\Catalog\view\frontend\product\price\final_price.phtml
 *  getMsrp() - /app\code\Magento\Catalog\view\frontend\product\price\msrp_item.phtml
 */
interface SaleableInterface
{
    /**
     * @return \Magento\Pricing\PriceInfoInterface
     */
    public function getPriceInfo();

    /**
     * @return string
     */
    public function getTypeId();

    /**
     * @return int
     */
    public function getId();
}
