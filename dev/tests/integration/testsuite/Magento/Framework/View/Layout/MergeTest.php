<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout;

use Magento\Framework\App\State;
use Magento\Framework\Phrase;
use Magento\Framework\View\Layout\LayoutCacheKeyInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MergeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Fixture XML instruction(s) to be used in tests
     */
    // @codingStandardsIgnoreStart
    const FIXTURE_LAYOUT_XML = '<block class="Magento\Framework\View\Element\Template" template="Magento_Framework::fixture_template_one.phtml"/>';
    // @codingStandardsIgnoreEnd

    /**
     * @var \Magento\Framework\View\Model\Layout\Merge
     */
    protected $_model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_resource;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_appState;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_cache;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_serializer;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_theme;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $scope;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_logger;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_layoutValidator;

    /**
     * @var \Magento\Framework\View\Page\Config|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $pageConfig;

    /**
     * @var LayoutCacheKeyInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $layoutCacheKeyMock;

    protected function setUp(): void
    {
        $files = [];
        $fileDriver = new \Magento\Framework\Filesystem\Driver\File();
        foreach ($fileDriver->readDirectory(__DIR__ . '/_mergeFiles/layout/') as $filename) {
            $files[] = new \Magento\Framework\View\File($filename, 'Magento_Widget');
        }
        $fileSource = $this->getMockForAbstractClass(\Magento\Framework\View\File\CollectorInterface::class);
        $fileSource->expects($this->any())->method('getFiles')->will($this->returnValue($files));

        $pageLayoutFileSource = $this->getMockForAbstractClass(\Magento\Framework\View\File\CollectorInterface::class);
        $pageLayoutFileSource->expects($this->any())->method('getFiles')->willReturn([]);

        $design = $this->getMockForAbstractClass(\Magento\Framework\View\DesignInterface::class);

        $this->scope = $this->createMock(\Magento\Framework\Url\ScopeInterface::class);
        $this->scope->expects($this->any())->method('getId')->will($this->returnValue(20));
        $scopeResolver = $this->getMockForAbstractClass(\Magento\Framework\Url\ScopeResolverInterface::class);
        $scopeResolver->expects($this->once())->method('getScope')->with(null)->will($this->returnValue($this->scope));

        $this->_resource = $this->createMock(\Magento\Widget\Model\ResourceModel\Layout\Update::class);

        $this->_appState = $this->createMock(\Magento\Framework\App\State::class);

        $this->_logger = $this->createMock(\Psr\Log\LoggerInterface::class);

        $this->_layoutValidator = $this->createMock(\Magento\Framework\View\Model\Layout\Update\Validator::class);

        $this->_cache = $this->getMockForAbstractClass(\Magento\Framework\Cache\FrontendInterface::class);

        $this->_serializer = $this->getMockForAbstractClass(\Magento\Framework\Serialize\SerializerInterface::class);

        $this->_theme = $this->createMock(\Magento\Theme\Model\Theme::class);
        $this->_theme->expects($this->any())->method('isPhysical')->will($this->returnValue(true));
        $this->_theme->expects($this->any())->method('getArea')->will($this->returnValue('area'));
        $this->_theme->expects($this->any())->method('getId')->will($this->returnValue(100));

        $objectHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->pageConfig = $this->getMockBuilder(\Magento\Framework\View\Page\Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $readFactory = $this->createMock(\Magento\Framework\Filesystem\File\ReadFactory::class);
        $fileReader = $this->createMock(\Magento\Framework\Filesystem\File\Read::class);
        $readFactory->expects($this->any())->method('create')->willReturn($fileReader);

        $fileDriver = $objectHelper->getObject(\Magento\Framework\Filesystem\Driver\File::class);

        $fileReader->expects($this->any())->method('readAll')->will(
            $this->returnCallback(
                function ($filename) use ($fileDriver) {
                    return $fileDriver->fileGetContents(__DIR__ . '/_mergeFiles/layout/' . $filename);
                }
            )
        );

        $this->layoutCacheKeyMock = $this->getMockForAbstractClass(LayoutCacheKeyInterface::class);
        $this->layoutCacheKeyMock->expects($this->any())
            ->method('getCacheKeys')
            ->willReturn([]);

        $this->_model = $objectHelper->getObject(
            \Magento\Framework\View\Model\Layout\Merge::class,
            [
                'design' => $design,
                'scopeResolver' => $scopeResolver,
                'fileSource' => $fileSource,
                'pageLayoutFileSource' => $pageLayoutFileSource,
                'resource' => $this->_resource,
                'appState' => $this->_appState,
                'cache' => $this->_cache,
                'serializer' => $this->_serializer,
                'theme' => $this->_theme,
                'validator' => $this->_layoutValidator,
                'logger' => $this->_logger,
                'readFactory' => $readFactory,
                'pageConfig' => $this->pageConfig,
                'layoutCacheKey' => $this->layoutCacheKeyMock,
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
            'existing page type' => ['default', true],
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
                    <block class="Magento\Framework\View\Element\Template"
                           template="Magento_Framework::fixture_template_one.phtml"/>
                </body>
                <body>
                    <block class="Magento\Framework\View\Element\Template"
                           template="Magento_Framework::fixture_template_two.phtml"/>
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
                        <block class="Magento\Framework\View\Element\Template"
                               template="Magento_Framework::fixture_template_one.phtml"/>
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
        $cacheValue = [
            "pageLayout" => "1column",
            "layout"     => self::FIXTURE_LAYOUT_XML
        ];

        $this->_cache->expects($this->at(0))->method('load')
            ->with('LAYOUT_area_STORE20_100c6a4ccd050e33acef0553f24ef399961_page_layout_merged')
            ->will($this->returnValue(json_encode($cacheValue)));

        $this->_serializer->expects($this->once())->method('unserialize')->willReturn($cacheValue);

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

    public function testLoadWithInvalidArgumentThrowsException()
    {
        $this->expectExceptionMessage("Invalid layout update handle");
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->_model->load(123);
    }

    /**
     * Test loading invalid layout
     **/
    public function testLoadWithInvalidLayout()
    {
        $this->expectExceptionMessage("Layout is invalid.");
        $this->expectException(\Exception::class);
        $this->_model->addPageHandles(['default']);

        $this->_appState->expects($this->once())->method('getMode')->willReturn(State::MODE_DEVELOPER);

        $this->_layoutValidator->expects($this->any())
            ->method('getMessages')
            ->willReturn(['testMessage1', 'testMessage2']);

        $this->_layoutValidator->expects($this->any())
            ->method('isValid')
            ->willThrowException(new \Exception('Layout is invalid.'));

        // phpcs:ignore Magento2.Security.InsecureFunction
        $suffix = md5(implode('|', $this->_model->getHandles()));
        $cacheId = "LAYOUT_{$this->_theme->getArea()}_STORE{$this->scope->getId()}"
            . "_{$this->_theme->getId()}{$suffix}_page_layout_merged";
        $messages = $this->_layoutValidator->getMessages();

        // Testing error message is logged with logger
        $this->_logger->expects($this->once())->method('info')
            ->with(
                'Cache file with merged layout: ' . $cacheId . ' and handles default' . ': ' . array_shift($messages)
            );

        $this->_model->load();
    }

    public function testLayoutUpdateFileIsNotValid()
    {
        $this->expectExceptionMessageMatches("/_mergeFiles\/layout\/file_wrong\.xml\' is not valid/");
        $this->expectException(\Magento\Framework\Config\Dom\ValidationException::class);
        $this->_appState->expects($this->once())->method('getMode')->willReturn(State::MODE_DEVELOPER);

        $this->_model->addPageHandles(['default']);
    }
}
