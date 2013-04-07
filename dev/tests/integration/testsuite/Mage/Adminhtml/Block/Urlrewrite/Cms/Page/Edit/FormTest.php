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

/**
 * Test for Mage_Adminhtml_Block_Urlrewrite_Cms_Page_Edit_FormTest
 */
class Mage_Adminhtml_Block_Urlrewrite_Cms_Page_Edit_FormTest extends Mage_Backend_Area_TestCase
{
    /**
     * Get form instance
     *
     * @param array $args
     * @return Varien_Data_Form
     */
    protected function _getFormInstance($args = array())
    {
        /** @var $layout Mage_Core_Model_Layout */
        $layout = Mage::getModel('Mage_Core_Model_Layout');
        /** @var $block Mage_Adminhtml_Block_Urlrewrite_Cms_Page_Edit_Form */
        $block = $layout->createBlock(
            'Mage_Adminhtml_Block_Urlrewrite_Cms_Page_Edit_Form', 'block', array('data' => $args)
        );
        $block->toHtml();
        return $block->getForm();
    }

    /**
     * Check _formPostInit set expected fields values
     *
     * @covers Mage_Adminhtml_Block_Urlrewrite_Cms_Page_Edit_Form::_formPostInit
     *
     * @dataProvider formPostInitDataProvider
     *
     * @param array $cmsPageData
     * @param string $action
     * @param string $idPath
     * @param string $requestPath
     * @param string $targetPath
     * @magentoConfigFixture current_store general/single_store_mode/enabled 1
     */
    public function testFormPostInit($cmsPageData, $action, $idPath, $requestPath, $targetPath)
    {
        $args = array();
        if ($cmsPageData) {
            $args['cms_page'] = new Varien_Object($cmsPageData);
        }
        $form = $this->_getFormInstance($args);
        $this->assertContains($action, $form->getAction());

        $this->assertEquals($idPath, $form->getElement('id_path')->getValue());
        $this->assertEquals($requestPath, $form->getElement('request_path')->getValue());
        $this->assertEquals($targetPath, $form->getElement('target_path')->getValue());

        $this->assertTrue($form->getElement('id_path')->getData('disabled'));
        $this->assertTrue($form->getElement('target_path')->getData('disabled'));
    }

    /**
     * Test entity stores
     *
     * @magentoAppIsolation enabled
     * @magentoDataFixture Mage/Core/_files/store.php
     */
    public function testGetEntityStores()
    {
        $args = array(
            'cms_page' => $this->_getCmsPageWithStoresMock(array(1))
        );
        $form = $this->_getFormInstance($args);

        $expectedStores = array(
            array(
                'label' => 'Main Website',
                'value' => array()
            ),
            array(
                'label' => '    Main Website Store',
                'value' => array(
                    array(
                        'label' => '    Default Store View',
                        'value' => 1
                    )
                )
            )
        );
        $this->assertEquals($expectedStores, $form->getElement('store_id')->getValues());
    }

    /**
     * Check exception is thrown when product does not associated with stores
     *
     * @magentoAppIsolation enabled
     * @magentoDataFixture Mage/Core/_files/store.php
     *
     * @expectedException Mage_Core_Model_Store_Exception
     * @expectedExceptionMessage Chosen cms page does not associated with any website.
     */
    public function testGetEntityStoresProductStoresException()
    {
        $args = array(
            'cms_page' => $this->_getCmsPageWithStoresMock(array())
        );
        $this->_getFormInstance($args);
    }

    /**
     * Data provider for testing formPostInit
     * 1) Cms page is selected
     *
     * @static
     * @return array
     */
    public static function formPostInitDataProvider()
    {
        return array(
            array(
                array('id' => 3, 'identifier' => 'cms-page'),
                'cms_page/3', 'cms_page/3', 'cms-page', 'cms/page/view/page_id/3'
            )
        );
    }

    /**
     * Get CMS page model mock
     *
     * @param $stores
     * @return PHPUnit_Framework_MockObject_MockObject|Mage_Cms_Model_Page
     */
    protected function _getCmsPageWithStoresMock($stores)
    {
        $resourceMock = $this->getMockBuilder('Mage_Cms_Model_Resource_Page')
            ->setMethods(array('lookupStoreIds'))
            ->disableOriginalConstructor()
            ->getMock();
        $resourceMock->expects($this->any())
            ->method('lookupStoreIds')
            ->will($this->returnValue($stores));

        $cmsPageMock = $this->getMockBuilder('Mage_Cms_Model_Page')
            ->setMethods(array('getResource', 'getId'))
            ->disableOriginalConstructor()
            ->getMock();
        $cmsPageMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));
        $cmsPageMock->expects($this->any())
            ->method('getResource')
            ->will($this->returnValue($resourceMock));

        return $cmsPageMock;
    }
}
