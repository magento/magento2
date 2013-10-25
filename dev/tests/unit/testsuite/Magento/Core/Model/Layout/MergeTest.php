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

namespace Magento\Core\Model\Layout;

class MergeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Fixture XML instruction(s) to be used in tests
     */
    const FIXTURE_LAYOUT_XML = '<block class="Magento\Core\Block\Template" template="fixture.phtml"/>';

    /**
     * @var \Magento\Core\Model\Layout\Merge
     */
    private $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_resource;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_appState;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_cache;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_theme;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_store;

    protected function setUp()
    {
        $files = array();
        foreach (glob(__DIR__ . '/_files/layout/*.xml') as $filename) {
            $files[] = new \Magento\Core\Model\Layout\File($filename, 'Magento_Core');
        }
        $fileSource = $this->getMockForAbstractClass('Magento\Core\Model\Layout\File\SourceInterface');
        $fileSource->expects($this->any())->method('getFiles')->will($this->returnValue($files));

        $design = $this->getMockForAbstractClass('Magento\View\DesignInterface');

        $this->_store = $this->getMock('Magento\Core\Model\Store', array(), array(), '', false);
        $this->_store->expects($this->any())->method('getId')->will($this->returnValue(20));
        $storeManager = $this->getMockForAbstractClass('Magento\Core\Model\StoreManagerInterface');
        $storeManager->expects($this->once())->method('getStore')->with(null)->will($this->returnValue($this->_store));

        $this->_resource = $this->getMock('Magento\Core\Model\Resource\Layout\Update', array(), array(), '', false);

        $this->_appState = $this->getMock('Magento\App\State', array(), array(), '', false);

        $this->_cache = $this->getMockForAbstractClass('Magento\Cache\FrontendInterface');

        $this->_theme = $this->getMock('Magento\Core\Model\Theme', array(), array(), '', false, false);
        $this->_theme->expects($this->any())->method('isPhysical')->will($this->returnValue(true));
        $this->_theme->expects($this->any())->method('getArea')->will($this->returnValue('area'));
        $this->_theme->expects($this->any())->method('getId')->will($this->returnValue(100));

        $objectHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_model = $objectHelper->getObject('Magento\Core\Model\Layout\Merge', array(
            'design' => $design,
            'storeManager' => $storeManager,
            'fileSource' => $fileSource,
            'resource' => $this->_resource,
            'appState' => $this->_appState,
            'cache' => $this->_cache,
            'theme' => $this->_theme,
        ));
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
        $this->assertInternalType('string', $actual['default']['label']);
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

    public function testLoadFileSystem()
    {
        $handles = array('fixture_handle_one', 'fixture_handle_two');
        $this->assertEmpty($this->_model->getHandles());
        $this->assertEmpty($this->_model->asString());
        $this->_model->load($handles);
        $this->assertEquals($handles, $this->_model->getHandles());
        $expectedResult = '
            <root>
                <block class="Magento\Core\Block\Template" template="fixture_template_one.phtml"/>
                <block class="Magento\Core\Block\Template" template="fixture_template_two.phtml"/>
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

    public function testGetContainers()
    {
        $this->_model->addPageHandles(array('catalog_product_view_type_configurable'));
        $this->_model->load();
        $expected = array(
            'content'                         => 'Main Content Area',
            'product.info.extrahint'          => 'Product View Extra Hint',
            'product.info.configurable.extra' => 'Configurable Product Extra Info',
        );
        $this->assertEquals($expected, $this->_model->getContainers());
    }
}
