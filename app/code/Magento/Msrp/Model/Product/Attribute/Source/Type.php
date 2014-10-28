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
namespace Magento\Msrp\Model\Product\Attribute\Source;

/**
 * Catalog product Msrp "Display Actual Price" attribute source
 */
class Type extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * Display Product Price on gesture
     */
    const TYPE_ON_GESTURE = 1;

    /**
     * Display Product Price in cart
     */
    const TYPE_IN_CART = 2;

    /**
     * Display Product Price before order confirmation
     */
    const TYPE_BEFORE_ORDER_CONFIRM = 3;

    /**
     * Get all options
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = array(
                array('label' => __('On Gesture'), 'value' => self::TYPE_ON_GESTURE),
                array('label' => __('In Cart'), 'value' => self::TYPE_IN_CART),
                array('label' => __('Before Order Confirmation'), 'value' => self::TYPE_BEFORE_ORDER_CONFIRM)
            );
        }
        return $this->_options;
    }
}
