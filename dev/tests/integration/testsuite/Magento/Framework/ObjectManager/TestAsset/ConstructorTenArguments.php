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
namespace Magento\Framework\ObjectManager\TestAsset;

/**
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class ConstructorTenArguments extends \Magento\Framework\ObjectManager\TestAsset\ConstructorNineArguments
{
    /**
     * @var \Magento\Framework\ObjectManager\TestAsset\Basic
     */
    protected $_ten;

    /**
     * Ten arguments
     *
     * @param \Magento\Framework\ObjectManager\TestAsset\Basic $one
     * @param \Magento\Framework\ObjectManager\TestAsset\Basic $two
     * @param \Magento\Framework\ObjectManager\TestAsset\Basic $three
     * @param \Magento\Framework\ObjectManager\TestAsset\Basic $four
     * @param \Magento\Framework\ObjectManager\TestAsset\Basic $five
     * @param \Magento\Framework\ObjectManager\TestAsset\Basic $six
     * @param \Magento\Framework\ObjectManager\TestAsset\Basic $seven
     * @param \Magento\Framework\ObjectManager\TestAsset\Basic $eight
     * @param \Magento\Framework\ObjectManager\TestAsset\Basic $nine
     * @param \Magento\Framework\ObjectManager\TestAsset\Basic $ten
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\ObjectManager\TestAsset\Basic $one,
        \Magento\Framework\ObjectManager\TestAsset\Basic $two,
        \Magento\Framework\ObjectManager\TestAsset\Basic $three,
        \Magento\Framework\ObjectManager\TestAsset\Basic $four,
        \Magento\Framework\ObjectManager\TestAsset\Basic $five,
        \Magento\Framework\ObjectManager\TestAsset\Basic $six,
        \Magento\Framework\ObjectManager\TestAsset\Basic $seven,
        \Magento\Framework\ObjectManager\TestAsset\Basic $eight,
        \Magento\Framework\ObjectManager\TestAsset\Basic $nine,
        \Magento\Framework\ObjectManager\TestAsset\Basic $ten
    ) {
        parent::__construct($one, $two, $three, $four, $five, $six, $seven, $eight, $nine);
        $this->_ten = $ten;
    }
}
