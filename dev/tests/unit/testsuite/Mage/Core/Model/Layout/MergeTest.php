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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Core_Model_Layout_MergeTest extends PHPUnit_Framework_TestCase
{
    /**
     * Fixture XML instruction(s) to be used in tests
     */
    const FIXTURE_LAYOUT_XML = '<block type="Mage_Core_Block_Template" template="fixture.phtml"/>';

    /**
     * @var Mage_Core_Model_Layout_Merge
     */
    private $_model;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $_resource;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $_appState;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $_cache;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $_theme;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $_store;

    protected function setUp()
    {
        $fileSource = $this->getMockForAbstractClass('Mage_Core_Model_Layout_File_SourceInterface');
        $fileSource->expects($this->any())->method('getFiles')->will($this->returnValue(array(
            new Mage_Core_Model_Layout_File(__DIR__ . '/_files/pages.xml', 'Mage_Core'),
            new Mage_Core_Model_Layout_File(__DIR__ . '/_files/handles.xml', 'Mage_Core'),
        )));

        $design = $this->getMockForAbstractClass('Mage_Core_Model_View_DesignInterface');

        $this->_store = $this->getMock('Mage_Core_Model_Store', array(), array(), '', false);
        $this->_store->expects($this->any())->method('getId')->will($this->returnValue(20));
        $storeManager = $this->getMockForAbstractClass('Mage_Core_Model_StoreManagerInterface');
        $storeManager->expects($this->once())->method('getStore')->with(null)->will($this->returnValue($this->_store));

        $this->_resource = $this->getMock('Mage_Core_Model_Resource_Layout_Update', array(), array(), '', false);

        $this->_appState = $this->getMock('Mage_Core_Model_App_State', array(), array(), '', false);

        $this->_cache = $this->getMockForAbstractClass('Magento_Cache_FrontendInterface');

        $this->_theme = $this->getMock('Mage_Core_Model_Theme', array(), array(), '', false, false);
        $this->_theme->expects($this->any())->method('isPhysical')->will($this->returnValue(true));
        $this->_theme->expects($this->any())->method('getArea')->will($this->returnValue('area'));
        $this->_theme->expects($this->any())->method('getId')->will($this->returnValue(100));

        $this->_model = new Mage_Core_Model_Layout_Merge(
            $design, $storeManager, $fileSource, $this->_resource, $this->_appState, $this->_cache, $this->_theme
        );
    }

    public function testAddUpdate()
    {
        $this->assertEmpty($this->_model->asArray());
        $this->assertEmpty($this->_model->asString());
        $this->_model->addUpdate('test');
        $this->assertEquals(array('test'), $this->_model->asArray());
        $this->assertEquals('test', $this->_model->asString());
    }

    public function testAddHandle()
    {
        $this->assertEmpty($this->_model->getHandles());
        $this->_model->addHandle('test');
        $this->assertEquals(array('test'), $this->_model->getHandles());
    }

    public function testRemoveHandle()
    {
        $this->_model->addHandle('test');
        $this->_model->removeHandle('test');
        $this->assertEmpty($this->_model->getHandles());
    }

    public function testAddPageHandles()
    {
        /* add a non-page handle to verify that it won't be affected during page handles manipulation */
        $nonPageHandles = array('non_page_handle');
        $this->_model->addHandle($nonPageHandles);

        $this->assertFalse($this->_model->addPageHandles(array('non_existing_handle')));
        $this->assertEmpty($this->_model->getPageHandles());
        $this->assertEquals($nonPageHandles, $this->_model->getHandles());

        /* test that only the first existing handle is taken into account */
        $handlesToTry = array('catalog_product_view_type_simple', 'checkout_onepage_index');
        $expectedPageHandles = array(
            'default', 'catalog_category_default', 'catalog_product_view', 'catalog_product_view_type_simple'
        );
        $this->assertTrue($this->_model->addPageHandles($handlesToTry));
        $this->assertEquals($expectedPageHandles, $this->_model->getPageHandles());
        $this->assertEquals(array_merge($nonPageHandles, $expectedPageHandles), $this->_model->getHandles());

        /* test that new handles override the previous ones */
        $expectedPageHandles = array('default', 'checkout_onepage_index');
        $this->assertTrue($this->_model->addPageHandles(array('checkout_onepage_index')));
        $this->assertEquals($expectedPageHandles, $this->_model->getPageHandles());
        $this->assertEquals(array_merge($nonPageHandles, $expectedPageHandles), $this->_model->getHandles());
    }

    /**
     * @param string $inputPageHandle
     * @param bool $isPageTypeOnly
     * @param array $expectedResult
     * @dataProvider getPageHandleParentsDataProvider
     */
    public function testGetPageHandleParents($inputPageHandle, $isPageTypeOnly, $expectedResult)
    {
        $this->assertSame($expectedResult, $this->_model->getPageHandleParents($inputPageHandle, $isPageTypeOnly));
    }

    public function getPageHandleParentsDataProvider()
    {
        return array(
            'non-existing handle'      => array('non_existing_handle', false, array()),
            'non page type handle'     => array('not_a_page_type', false, array()),
            'page type with no parent' => array('default', false, array()),
            'page type with parent'    => array(
                'catalog_category_default', false, array('default')
            ),
            'deeply nested page type'  => array(
                'catalog_category_layered', false, array('default', 'catalog_category_default')
            ),
            'page fragment is not processed' => array(
                'checkout_onepage_progress', true, array()
            ),
            'page fragment is processed' => array(
                'checkout_onepage_progress', false, array('default', 'checkout_onepage_index')
            )
        );
    }

    public function testGetPageHandlesHierarchy()
    {
        $expected = require(__DIR__ . '/_files/pages_hierarchy.php');
        $actual = $this->_model->getPageHandlesHierarchy();
        $this->assertEquals($expected, $actual);
    }

    /**
     * @dataProvider pageHandleExistsDataProvider
     */
    public function testPageHandleExists($inputPageHandle, $expectedResult)
    {
        $this->assertSame($expectedResult, $this->_model->pageHandleExists($inputPageHandle));
    }

    public function pageHandleExistsDataProvider()
    {
        return array(
            'non-existing handle'  => array('non_existing_handle', false),
            'non page type handle' => array('not_a_page_type',     false),
            'existing page type'   => array('default',             true),
        );
    }

    /**
     * @dataProvider getPageHandleLabelDataProvider
     */
    public function testGetPageHandleLabel($inputPageType, $expectedResult)
    {
        $this->assertSame($expectedResult, $this->_model->getPageHandleLabel($inputPageType));
    }

    public function getPageHandleLabelDataProvider()
    {
        return array(
            'non-existing handle'  => array('non_existing_handle', null),
            'non page type handle' => array('not_a_page_type',     null),
            'existing page type'   => array('default',             'All Pages'),
        );
    }

    public function testLoadFileSystem()
    {
        $handles = array('fixture_handle_one', 'fixture_handle_two');
        $this->assertEmpty($this->_model->getHandles());
        $this->assertEmpty($this->_model->asString());
        $this->_model->load($handles);
        $this->assertEquals($handles, $this->_model->getHandles());
        $expectedResult = '
            <root>
                <block type="Mage_Core_Block_Template" template="fixture_template_one.phtml"/>
                <block type="Mage_Core_Block_Template" template="fixture_template_two.phtml"/>
            </root>
        ';
        $actualResult = '<root>' . $this->_model->asString() . '</root>';
        $this->assertXmlStringEqualsXmlString($expectedResult, $actualResult);
    }

    public function testLoadCache()
    {
        $this->_cache
            ->expects($this->at(0))
            ->method('load')
            ->with('LAYOUT_area_STORE20_100c6a4ccd050e33acef0553f24ef399961')
            ->will($this->returnValue(self::FIXTURE_LAYOUT_XML))
        ;
        $this->assertEmpty($this->_model->getHandles());
        $this->assertEmpty($this->_model->asString());
        $handles = array('fixture_handle_one', 'fixture_handle_two');
        $this->_model->load($handles);
        $this->assertEquals($handles, $this->_model->getHandles());
        $this->assertEquals(self::FIXTURE_LAYOUT_XML, $this->_model->asString());
    }

    public function testLoadDbAppInstalled()
    {
        $this->_appState->expects($this->any())->method('isInstalled')->will($this->returnValue(true));
        $this->_resource
            ->expects($this->once())
            ->method('fetchUpdatesByHandle')
            ->with('fixture_handle', $this->_theme, $this->_store)
            ->will($this->returnValue(self::FIXTURE_LAYOUT_XML))
        ;
        $this->assertEmpty($this->_model->getHandles());
        $this->assertEmpty($this->_model->asString());
        $handles = array('fixture_handle');
        $this->_model->load($handles);
        $this->assertEquals($handles, $this->_model->getHandles());
        $this->assertXmlStringEqualsXmlString(self::FIXTURE_LAYOUT_XML, $this->_model->asString());
    }

    public function testLoadDbAppNotInstalled()
    {
        $this->_appState->expects($this->any())->method('isInstalled')->will($this->returnValue(false));
        $this->_resource->expects($this->never())->method('fetchUpdatesByHandle');
        $this->assertEmpty($this->_model->getHandles());
        $this->assertEmpty($this->_model->asString());
        $handles = array('fixture_handle');
        $this->_model->load($handles);
        $this->assertEquals($handles, $this->_model->getHandles());
        $this->assertEmpty($this->_model->asString());
    }

    public function testGetFileLayoutUpdatesXml()
    {
        $actualXml = $this->_model->getFileLayoutUpdatesXml();
        $this->assertXmlStringEqualsXmlFile(__DIR__ . '/_files/merged.xml', $actualXml->asNiceXml());
    }
}
