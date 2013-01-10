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
 * @package     Magento_Di
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Magento_Di_TestAsset_ConstructorNineArguments extends Magento_Di_TestAsset_ConstructorEightArguments
{
    /**
     * @var Magento_Di_TestAsset_Basic
     */
    protected $_nine;

    /**
     * Nine arguments
     *
     * @param Magento_Di_TestAsset_Basic $one
     * @param Magento_Di_TestAsset_Basic $two
     * @param Magento_Di_TestAsset_Basic $three
     * @param Magento_Di_TestAsset_Basic $four
     * @param Magento_Di_TestAsset_Basic $five
     * @param Magento_Di_TestAsset_Basic $six
     * @param Magento_Di_TestAsset_Basic $seven
     * @param Magento_Di_TestAsset_Basic $eight
     * @param Magento_Di_TestAsset_Basic $nine
     */
    public function __construct(
        Magento_Di_TestAsset_Basic $one,
        Magento_Di_TestAsset_Basic $two,
        Magento_Di_TestAsset_Basic $three,
        Magento_Di_TestAsset_Basic $four,
        Magento_Di_TestAsset_Basic $five,
        Magento_Di_TestAsset_Basic $six,
        Magento_Di_TestAsset_Basic $seven,
        Magento_Di_TestAsset_Basic $eight,
        Magento_Di_TestAsset_Basic $nine
    ) {
        parent::__construct($one, $two, $three, $four, $five, $six, $seven, $eight);
        $this->_nine = $nine;
    }
}
