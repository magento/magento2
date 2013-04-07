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
 * @package     Mage_Adminhtml
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Super_SettingsTest extends Mage_Backend_Area_TestCase
{
    /**
     * @param null|int $productId
     * @param string $expectedUrl
     *
     * @magentoAppIsolation enabled
     * @dataProvider getContinueUrlDataProvider
     */
    public function testGetContinueUrl($productId, $expectedUrl)
    {
        $product = $this->getMockBuilder('Mage_Catalog_Model_Product')
            ->disableOriginalConstructor()
            ->setMethods(array('getId'))
            ->getMock();
        $product->expects($this->any())->method('getId')->will($this->returnValue($productId));

        $urlModel = $this->getMockBuilder('Mage_Backend_Model_Url')
            ->disableOriginalConstructor()
            ->setMethods(array('getUrl'))
            ->getMock();
        $urlModel->expects($this->any())->method('getUrl')->with($this->equalTo($expectedUrl))
            ->will($this->returnValue('url'));

        Mage::register('current_product', $product);

        $context = Mage::getModel('Mage_Core_Block_Template_Context', array('urlBuilder' => $urlModel));
        /** @var $layout Mage_Core_Model_Layout */
        $layout = Mage::getModel('Mage_Core_Model_Layout');
        /** @var $block Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Super_Settings */
        $block = $layout->createBlock(
            'Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Super_Settings',
            'block',
            array(
               'context' => $context
            )
        );
        $this->assertEquals('url', $block->getContinueUrl());
    }

    /**
     * @return array
     */
    public static function getContinueUrlDataProvider()
    {
        return array(
            array(null, '*/*/new'),
            array(1, '*/*/edit'),
        );
    }
}
