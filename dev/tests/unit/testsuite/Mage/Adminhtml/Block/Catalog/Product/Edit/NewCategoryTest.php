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
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Adminhtml_Block_Catalog_Product_Edit_NewCategoryTest extends PHPUnit_Framework_TestCase
{
    /** @var Mage_Adminhtml_Block_Catalog_Product_Edit_NewCategory */
    protected $_object;

    /** @var Mage_Core_Model_Url|PHPUnit_Framework_MockObject_MockObject */
    protected $_urlModel;

    protected function setUp()
    {
        $objectManager = new Magento_Test_Helper_ObjectManager($this);

        $this->_urlModel = $this->getMock('Mage_Backend_Model_Url', array('getUrl'), array(), '', false);
        $this->_object = $objectManager->getBlock('Mage_Adminhtml_Block_Catalog_Product_Edit_NewCategory', array(
            'urlBuilder' => $this->_urlModel,
        ));
    }

    /**
     * @covers Mage_Adminhtml_Block_Catalog_Product_Edit_NewCategory::getSaveCategoryUrl
     * @covers Mage_Adminhtml_Block_Catalog_Product_Edit_NewCategory::getSuggestCategoryUrl
     * @dataProvider urlMethodsDataProvider
     * @param string $expectedUrl
     * @param string $executedMethod
     */
    public function testGetUrlMethods($expectedUrl, $executedMethod)
    {
        $this->_urlModel->expects($this->once())
            ->method('getUrl')
            ->with($expectedUrl)
            ->will($this->returnCallback(
                function ($string) {
                    return strrev($string);
                }
            ));
        $this->assertEquals(
            strrev($expectedUrl),
            call_user_func_array(array($this->_object, $executedMethod), array($expectedUrl))
        );
    }

    /**
     * @return array
     */
    public static function urlMethodsDataProvider()
    {
        return array(
            array('*/catalog_category/save', 'getSaveCategoryUrl'),
            array('*/catalog_category/suggestCategories', 'getSuggestCategoryUrl'),
        );
    }
}
