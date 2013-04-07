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
 * @package     Magento_ObjectManager
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Magento_ObjectManager_TestAsset_ConstructorTenArguments
    extends Magento_ObjectManager_TestAsset_ConstructorNineArguments
{
    /**
     * @var Magento_ObjectManager_TestAsset_Basic
     */
    protected $_ten;

    /**
     * Ten arguments
     *
     * @param Magento_ObjectManager_TestAsset_Basic $one
     * @param Magento_ObjectManager_TestAsset_Basic $two
     * @param Magento_ObjectManager_TestAsset_Basic $three
     * @param Magento_ObjectManager_TestAsset_Basic $four
     * @param Magento_ObjectManager_TestAsset_Basic $five
     * @param Magento_ObjectManager_TestAsset_Basic $six
     * @param Magento_ObjectManager_TestAsset_Basic $seven
     * @param Magento_ObjectManager_TestAsset_Basic $eight
     * @param Magento_ObjectManager_TestAsset_Basic $nine
     * @param Magento_ObjectManager_TestAsset_Basic $ten
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Magento_ObjectManager_TestAsset_Basic $one,
        Magento_ObjectManager_TestAsset_Basic $two,
        Magento_ObjectManager_TestAsset_Basic $three,
        Magento_ObjectManager_TestAsset_Basic $four,
        Magento_ObjectManager_TestAsset_Basic $five,
        Magento_ObjectManager_TestAsset_Basic $six,
        Magento_ObjectManager_TestAsset_Basic $seven,
        Magento_ObjectManager_TestAsset_Basic $eight,
        Magento_ObjectManager_TestAsset_Basic $nine,
        Magento_ObjectManager_TestAsset_Basic $ten
    ) {
        parent::__construct($one, $two, $three, $four, $five, $six, $seven, $eight, $nine);
        $this->_ten = $ten;
    }
}
