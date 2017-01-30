<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout;

use Magento\Framework\Phrase;

class MergeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Fixture XML instruction(s) to be used in tests
     */
    // @codingStandardsIgnoreStart
    const FIXTURE_LAYOUT_XML = '<block class="Magento\Framework\View\Element\Template" template="fixture_template_one.phtml"/>';
    // @codingStandardsIgnoreEnd

    /**
     * @var \Magento\Framework\View\Model\Layout\Merge
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_resource;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_appState;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cache;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_theme;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $scope;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_logger;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_layoutValidator;

    /**
     * @var \Magento\Framework\View\Page\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageConfig;

    protected function setUp()
    {
        $files = [];
        foreach (glob(__DIR__ . '/_mergeFiles/layout/*.xml') as $filename) {
            $files[] = new \Magento\Framework\View\File($filename, 'Magento_Widget');
        }
        $fileSource = $this->getMockForAbstractClass('Magento\Framework\View\File\CollectorInterface');
        $fileSource->expects($this->any())->method('getFiles')->will($this->returnValue($files));

        $pageLayoutFileSource = $this->getMockForAbstractClass('Magento\Framework\View\File\CollectorInterface');
        $pageLayoutFileSource->expects($this->any())->method('getFiles')->willReturn([]);

        $design = $this->getMockForAbstractClass('Magento\Framework\View\DesignInterface');

        $this->scope = $this->getMock('Magento\Framework\Url\ScopeInterface', [], [], '', false);
        $this->scope->expects($this->any())->method('getId')->will($this->returnValue(20));
        $scopeResolver = $this->getMockForAbstractClass('Magento\Framework\Url\ScopeResolverInterface');
        $scopeResolver->expects($this->once())->method('getScope')->with(null)->will($this->returnValue($this->scope));

        $this->_resource = $this->getMock('Magento\Widget\Model\ResourceModel\Layout\Update', [], [], '', false);

        $this->_appState = $this->getMock('Magento\Framework\App\State', [], [], '', false);

        $this->_logger = $this->getMock('Psr\Log\LoggerInterface');

        $this->_layoutValidator = $this->getMock(
            'Magento\Framework\View\Model\Layout\Update\Validator',
            [],
            [],
            '',
            false
        );

        $this->_cache = $this->getMockForAbstractClass('Magento\Framework\Cache\FrontendInterface');

        $this->_theme = $this->getMock('Magento\Theme\Model\Theme', [], [], '', false, false);
        $this->_theme->expects($this->any())->method('isPhysical')->will($this->returnValue(true));
        $this->_theme->expects($this->any())->method('getArea')->will($this->returnValue('area'));
        $this->_theme->expects($this->any())->method('getId')->will($this->returnValue(100));

        $objectHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->pageConfig = $this->getMockBuilder('Magento\Framework\View\Page\Config')
            ->disableOriginalConstructor()
            ->getMock();

        $readFactory = $this->getMock('Magento\Framework\Filesystem\File\ReadFactory', [], [], '', false, false);
        $fileReader = $this->getMock('Magento\Framework\Filesystem\File\Read', [], [], '', false, false);
        $readFactory->expects($this->any())->method('create')->willReturn($fileReader);

        $fileDriver = $objectHelper->getObject('Magento\Framework\Filesystem\Driver\File');

        $fileReader->expects($this->any())->method('readAll')->will(
            $this->returnCallback(
                function ($filename) use ($fileDriver) {
                    return $fileDriver->fileGetContents(__DIR__ . '/_mergeFiles/layout/'. $filename);
                }
            )
        );

        $this->_model = $objectHelper->getObject(
            'Magento\Framework\View\Model\Layout\Merge',
            [
                'design' => $design,
                'scopeResolver' => $scopeResolver,
                'fileSource' => $fileSource,
                'pageLayoutFileSource' => $pageLayoutFileSource,
                'resource' => $this->_resource,
                'appState' => $this->_appState,
                'cache' => $this->_cache,
                'theme' => $this->_theme,
                'validator' => $this->_layoutValidator,
                'logger' => $this->_logger,
                'readFactory' => $readFactory,
                'pageConfig' => $this->pageConfig
            ]
        );
    }

    public function testAddUpdate()
    {
        $this->assertEmpty($this->_model->asArray());
        $this->assertEmpty($this->_model->asString());
        $this->_model->addUpdate('test');
        $this->assertEquals(['test'], $this->_model->asArray());
        $this->assertEquals('test', $this->_model->asString());
    }

    public function testAddHandle()
    {
        $this->assertEmpty($this->_model->getHandles());
        $this->_model->addHandle('test');
        $this->assertEquals(['test'], $this->_model->getHandles());
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
        $nonPageHandles = ['non_page_handle'];
        $this->_model->addHandle($nonPageHandles);

        $this->assertFalse($this->_model->addPageHandles(['non_existing_handle']));
        $this->assertEmpty($this->_model->getPageHandles());
        $this->assertEquals($nonPageHandles, $this->_model->getHandles());

        /* test that only the first existing handle is taken into account */
        $handlesToTry = [
            'default',
            'catalog_category_default',
            'catalog_product_view',
            'catalog_product_view_type_simple',
        ];
        $expectedPageHandles = [
            'default',
            'catalog_category_default',
            'catalog_product_view',
            'catalog_product_view_type_simple',
        ];
        $this->assertTrue($this->_model->addPageHandles($handlesToTry));
        $this->assertEquals($expectedPageHandles, $this->_model->getPageHandles());
        $this->assertEquals(array_merge($nonPageHandles, $expectedPageHandles), $this->_model->getHandles());

        /* test that new handles override the previous ones */
        $expectedPageHandles = ['default', 'checkout_index_index'];
        $this->_model->removeHandle('catalog_category_default');
        $this->_model->removeHandle('catalog_product_view');
        $this->_model->removeHandle('catalog_product_view_type_simple');
        $this->assertTrue($this->_model->addPageHandles(['default', 'checkout_index_index']));
        $this->assertEquals($expectedPageHandles, $this->_model->getPageHandles());
        $this->assertEquals(array_merge($nonPageHandles, $expectedPageHandles), $this->_model->getHandles());
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
        return [
            'non-existing handle' => ['non_existing_handle', false],
            'existing page type' => ['default', true]
        ];
    }

    public function testLoadFileSystem()
    {
        $handles = ['fixture_handle_one', 'fixture_handle_two'];
        $this->assertEmpty($this->_model->getHandles());
        $this->assertEmpty($this->_model->asString());
        $this->_model->load($handles);
        $this->assertEquals($handles, $this->_model->getHandles());
        $expectedResult = '
            <root>
                <body>
                    <block class="Magento\Framework\View\Element\Template" template="fixture_template_one.phtml"/>
                </body>
                <body>
                    <block class="Magento\Framework\View\Element\Template" template="fixture_template_two.phtml"/>
                </body>
            </root>
        ';
        $actualResult = '<root>' . $this->_model->asString() . '</root>';
        $this->assertXmlStringEqualsXmlString($expectedResult, $actualResult);
    }

    public function testLoadFileSystemWithPageLayout()
    {
        $handles = ['fixture_handle_with_page_layout'];
        $expectedHandles = ['fixture_handle_with_page_layout'];
        $expectedResult = '
            <root>
                <body>
                    <referenceContainer name="main.container">
                        <block class="Magento\Framework\View\Element\Template" template="fixture_template_one.phtml"/>
                    </referenceContainer>
                </body>
            </root>
        ';

        $this->assertEmpty($this->_model->getHandles());
        $this->assertEmpty($this->_model->asString());
        $this->_model->load($handles);

        $this->assertEquals($expectedHandles, $this->_model->getHandles());
        $actualResult = '<root>' . $this->_model->asString() . '</root>';
        $this->assertXmlStringEqualsXmlString($expectedResult, $actualResult);
        $this->assertEquals('fixture_handle_page_layout', $this->_model->getPageLayout());
    }

    public function testLoadCache()
    {
        $this->_cache->expects($this->at(0))->method('load')
            ->with('LAYOUT_area_STORE20_100c6a4ccd050e33acef0553f24ef399961')
            ->will($this->returnValue(self::FIXTURE_LAYOUT_XML));

        $this->assertEmpty($this->_model->getHandles());
        $this->assertEmpty($this->_model->asString());
        $handles = ['fixture_handle_one', 'fixture_handle_two'];
        $this->_model->load($handles);
        $this->assertEquals($handles, $this->_model->getHandles());
        $this->assertEquals(self::FIXTURE_LAYOUT_XML, $this->_model->asString());
    }

    public function testLoadDbApp()
    {
        $this->_resource->expects(
            $this->any()
        )->method(
            'fetchUpdatesByHandle'
        )->with(
            'fixture_handle',
            $this->_theme,
            $this->scope
        )->will(
            $this->returnValue(self::FIXTURE_LAYOUT_XML)
        );
        $this->assertEmpty($this->_model->getHandles());
        $this->assertEmpty($this->_model->asString());
        $handles = ['fixture_handle_one'];
        $this->_model->load($handles);
        $this->assertEquals($handles, $this->_model->getHandles());
        $this->assertXmlStringEqualsXmlString(
            '<body>' . self::FIXTURE_LAYOUT_XML . '</body>',
            $this->_model->asString()
        );
    }

    public function testGetFileLayoutUpdatesXml()
    {
        $errorString = "Theme layout update file '" . __DIR__ . "/_mergeFiles/layout/file_wrong.xml' is not valid.";
        $this->_logger->expects($this->atLeastOnce())->method('info')
            ->with($this->stringStartsWith($errorString));

        $actualXml = $this->_model->getFileLayoutUpdatesXml();
        $this->assertXmlStringEqualsXmlFile(__DIR__ . '/_mergeFiles/merged.xml', $actualXml->asNiceXml());
    }

    public function testGetContainers()
    {
        $this->_model->addPageHandles(['default']);
        $this->_model->addPageHandles(['catalog_product_view']);
        $this->_model->addPageHandles(['catalog_product_view_type_configurable']);
        $this->_model->load();
        $expected = [
            'content' => 'Main Content Area',
            'product.info.extrahint' => 'Product View Extra Hint',
            'product.info.configurable.extra' => 'Configurable Product Extra Info',
        ];
        $this->assertEquals($expected, $this->_model->getContainers());
    }

    public function testGetAllDesignAbstractions()
    {
        $expected = [
            'customer_account' => [
                'name' => 'customer_account',
                'label' => new Phrase('Customer My Account (All Pages)'),
                'design_abstraction' => 'custom',
            ],
            'page_empty' => [
                'name' => 'page_empty',
                'label' => new Phrase('All Empty Layout Pages'),
                'design_abstraction' => 'page_layout',
            ],
        ];

        $this->assertEquals($expected, $this->_model->getAllDesignAbstractions());
    }

    public function testIsPageLayoutDesignAbstractions()
    {
        $expected = [
            'customer_account' => [
                'name' => 'customer_account',
                'label' => 'Customer My Account (All Pages)',
                'design_abstraction' => 'custom',
            ],
            'page_empty' => [
                'name' => 'page_empty',
                'label' => 'All Empty Layout Pages',
                'design_abstraction' => 'page_layout',
            ],
            'empty_data' => [],
        ];

        $this->assertTrue($this->_model->isPageLayoutDesignAbstraction($expected['page_empty']));
        $this->assertFalse($this->_model->isPageLayoutDesignAbstraction($expected['customer_account']));
        $this->assertFalse($this->_model->isPageLayoutDesignAbstraction($expected['empty_data']));
    }

    public function testIsCustomDesignAbstractions()
    {
        $expected = [
            'customer_account' => [
                'name' => 'customer_account',
                'label' => 'Customer My Account (All Pages)',
                'design_abstraction' => 'custom',
            ],
            'page_empty' => [
                'name' => 'page_empty',
                'label' => 'All Empty Layout Pages',
                'design_abstraction' => 'page_layout',
            ],
            'empty_data' => [],
        ];
        $this->assertTrue($this->_model->isCustomerDesignAbstraction($expected['customer_account']));
        $this->assertFalse($this->_model->isCustomerDesignAbstraction($expected['page_empty']));
        $this->assertFalse($this->_model->isCustomerDesignAbstraction($expected['empty_data']));
    }

    /**
     * @expectedException        \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Invalid layout update handle
     */
    public function testLoadWithInvalidArgumentThrowsException()
    {
        $this->_model->load(123);
    }

    /**
     * Test loading invalid layout
     */
    public function testLoadWithInvalidLayout()
    {
        $this->_model->addPageHandles(['default']);

        $this->_appState->expects($this->any())->method('getMode')->will($this->returnValue('developer'));

        $this->_layoutValidator->expects($this->any())->method('getMessages')
            ->will($this->returnValue(['testMessage1', 'testMessage2']));

        $this->_layoutValidator->expects($this->any())->method('isValid')->will($this->returnValue(false));

        $suffix = md5(implode('|', $this->_model->getHandles()));
        $cacheId = "LAYOUT_{$this->_theme->getArea()}_STORE{$this->scope->getId()}_{$this->_theme->getId()}{$suffix}";
        $messages = $this->_layoutValidator->getMessages();

        // Testing error message is logged with logger
        $this->_logger->expects($this->once())->method('info')
            ->with(
                'Cache file with merged layout: ' . $cacheId . ' and handles default' . ': ' . array_shift($messages)
            );

        $this->_model->load();
    }
}
