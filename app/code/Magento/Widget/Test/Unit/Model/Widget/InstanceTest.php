<?php
/**
 * \Magento\Widget\Model\Widget\Instance
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Test\Unit\Model\Widget;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InstanceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Widget\Model\Config\Data|PHPUnit_Framework_MockObject_MockObject
     */
    private $widgetModelMock;

    /**
     * @var \Magento\Framework\View\FileSystem|PHPUnit_Framework_MockObject_MockObject
     */
    private $viewFileSystemMock;

    /**
     * @var  \Magento\Widget\Model\NamespaceResolver |PHPUnit_Framework_MockObject_MockObject
     */
    private $namespaceResolver;

    /**
     * @var \Magento\Widget\Model\Widget\Instance
     */
    private $model;

    /**
     * @var  \Magento\Widget\Model\Config\Reader
     */
    private $readerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $cacheTypesListMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $directoryMock;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventManagerMock;

    /**
     * @var \Psr\Log\LoggerInterface | PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerInterfaceMock;

    protected function setUp()
    {
        $this->widgetModelMock = $this->getMockBuilder(
            \Magento\Widget\Model\Widget::class
        )->disableOriginalConstructor()->getMock();
        $this->viewFileSystemMock = $this->getMockBuilder(
            \Magento\Framework\View\FileSystem::class
        )->disableOriginalConstructor()->getMock();
        $this->namespaceResolver = $this->getMockBuilder(
            \Magento\Widget\Model\NamespaceResolver::class
        )->disableOriginalConstructor()->getMock();
        $this->cacheTypesListMock = $this->getMock(\Magento\Framework\App\Cache\TypeListInterface::class);
        $this->readerMock = $this->getMockBuilder(
            \Magento\Widget\Model\Config\Reader::class
        )->disableOriginalConstructor()->getMock();
        $this->loggerInterfaceMock = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventManagerMock = $this->getMockBuilder(\Magento\Framework\Event\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $filesystemMock = $this->getMock(\Magento\Framework\Filesystem::class, [], [], '', false);
        $this->directoryMock = $this->getMock(
            \Magento\Framework\Filesystem\Directory\Read::class,
            [],
            [],
            '',
            false
        );
        $filesystemMock->expects(
            $this->any()
        )->method(
            'getDirectoryRead'
        )->will(
            $this->returnValue($this->directoryMock)
        );
        $this->directoryMock->expects($this->any())->method('isReadable')->will($this->returnArgument(0));
        $this->directoryMock->expects($this->any())->method('getRelativePath')->will($this->returnArgument(0));

        /* @var \Magento\Framework\Model\Context | PHPUnit_Framework_MockObject_MockObject */
        $contextMock = $this->getMockBuilder(\Magento\Framework\Model\Context::class)
            ->setMethods(['getLogger', 'getEventDispatcher'])
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock->expects($this->any())
            ->method('getLogger')
            ->will($this->returnValue($this->loggerInterfaceMock));
        $contextMock->expects($this->any())
            ->method('getEventDispatcher')
            ->will($this->returnValue($this->eventManagerMock));

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $args = $objectManagerHelper->getConstructArguments(
            \Magento\Widget\Model\Widget\Instance::class,
            [
                'context' => $contextMock,
                'filesystem' => $filesystemMock,
                'viewFileSystem' => $this->viewFileSystemMock,
                'cacheTypeList' => $this->cacheTypesListMock,
                'reader' => $this->readerMock,
                'widgetModel' => $this->widgetModelMock,
                'namespaceResolver' => $this->namespaceResolver
            ]
        );
        /** @var \Magento\Widget\Model\Widget\Instance model */
        $this->model = $this->getMock(
            \Magento\Widget\Model\Widget\Instance::class,
            ['_construct', 'getData', 'setData'],
            $args,
            '',
            true
        );
    }

    public function testGetWidgetConfigAsArray()
    {
        $widget = [
            '@' => ['type' => 'Magento\Cms\Block\Widget\Page\Link', 'module' => 'Magento_Cms'],
            'name' => 'CMS Page Link',
            'description' => 'Link to a CMS Page',
            'is_email_compatible' => 'true',
            'placeholder_image' => 'Magento_Cms::images/widget_page_link.png',
            'parameters' => [
                'page_id' => [
                    '@' => ['type' => 'complex'],
                    'type' => 'label',
                    'helper_block' => [
                        'type' => 'Magento\Cms\Block\Adminhtml\Page\Widget\Chooser',
                        'data' => ['button' => ['open' => 'Select Page...']],
                    ],
                    'visible' => 'true',
                    'required' => 'true',
                    'sort_order' => '10',
                    'label' => 'CMS Page',
                ],
            ],
        ];
        $this->widgetModelMock->expects(
            $this->once()
        )->method(
            'getWidgetByClassType'
        )->will(
            $this->returnValue($widget)
        );
        $xmlFile = __DIR__ . '/../_files/widget.xml';
        $this->viewFileSystemMock->expects($this->once())->method('getFilename')->will($this->returnValue($xmlFile));
        $themeConfigFile = __DIR__ . '/../_files/mappedConfigArrayAll.php';
        $themeConfig = include $themeConfigFile;
        $this->readerMock->expects(
            $this->once()
        )->method(
            'readFile'
        )->with(
            $this->equalTo($xmlFile)
        )->will(
            $this->returnValue($themeConfig)
        );

        $result = $this->model->getWidgetConfigAsArray();

        $expectedConfigFile = __DIR__ . '/../_files/mappedConfigArray1.php';
        $expectedConfig = include $expectedConfigFile;
        $this->assertEquals($expectedConfig, $result);
    }

    public function testGetWidgetTemplates()
    {
        $expectedConfigFile = __DIR__ . '/../_files/mappedConfigArray1.php';
        $widget = include $expectedConfigFile;
        $this->widgetModelMock->expects(
            $this->once()
        )->method(
            'getWidgetByClassType'
        )->will(
            $this->returnValue($widget)
        );
        $this->viewFileSystemMock->expects($this->once())->method('getFilename')->will($this->returnValue(''));
        $expectedTemplates = [
            'default' => [
                'value' => 'product/widget/link/link_block.phtml',
                'label' => 'Product Link Block Template',
            ],
            'link_inline' => [
                'value' => 'product/widget/link/link_inline.phtml',
                'label' => 'Product Link Inline Template',
            ],
        ];
        $this->assertEquals($expectedTemplates, $this->model->getWidgetTemplates());
    }

    public function testGetWidgetTemplatesValueOnly()
    {
        $widget = [
            '@' => ['type' => 'Magento\Cms\Block\Widget\Page\Link', 'module' => 'Magento_Cms'],
            'name' => 'CMS Page Link',
            'description' => 'Link to a CMS Page',
            'is_email_compatible' => 'true',
            'placeholder_image' => 'Magento_Cms::images/widget_page_link.png',
            'parameters' => [
                'template' => [
                    'values' => [
                        'default' => ['value' => 'product/widget/link/link_block.phtml', 'label' => 'Template'],
                    ],
                    'type' => 'select',
                    'visible' => 'true',
                    'label' => 'Template',
                    'value' => 'product/widget/link/link_block.phtml',
                ],
            ],
        ];
        $this->widgetModelMock->expects(
            $this->once()
        )->method(
            'getWidgetByClassType'
        )->will(
            $this->returnValue($widget)
        );
        $this->viewFileSystemMock->expects($this->once())->method('getFilename')->will($this->returnValue(''));
        $expectedTemplates = [
            'default' => ['value' => 'product/widget/link/link_block.phtml', 'label' => 'Template'],
        ];
        $this->assertEquals($expectedTemplates, $this->model->getWidgetTemplates());
    }

    public function testGetWidgetTemplatesNoTemplate()
    {
        $widget = [
            '@' => ['type' => 'Magento\Cms\Block\Widget\Page\Link', 'module' => 'Magento_Cms'],
            'name' => 'CMS Page Link',
            'description' => 'Link to a CMS Page',
            'is_email_compatible' => 'true',
            'placeholder_image' => 'Magento_Cms::images/widget_page_link.png',
            'parameters' => [],
        ];
        $this->widgetModelMock->expects(
            $this->once()
        )->method(
            'getWidgetByClassType'
        )->will(
            $this->returnValue($widget)
        );
        $this->viewFileSystemMock->expects($this->once())->method('getFilename')->will($this->returnValue(''));
        $expectedTemplates = [];
        $this->assertEquals($expectedTemplates, $this->model->getWidgetTemplates());
    }

    public function testGetWidgetSupportedContainers()
    {
        $expectedConfigFile = __DIR__ . '/../_files/mappedConfigArray1.php';
        $widget = include $expectedConfigFile;
        $this->widgetModelMock->expects(
            $this->once()
        )->method(
            'getWidgetByClassType'
        )->will(
            $this->returnValue($widget)
        );
        $this->viewFileSystemMock->expects($this->once())->method('getFilename')->will($this->returnValue(''));
        $expectedContainers = ['left', 'content'];
        $this->assertEquals($expectedContainers, $this->model->getWidgetSupportedContainers());
    }

    public function testGetWidgetSupportedContainersNoContainer()
    {
        $widget = [
            '@' => ['type' => 'Magento\Cms\Block\Widget\Page\Link', 'module' => 'Magento_Cms'],
            'name' => 'CMS Page Link',
            'description' => 'Link to a CMS Page',
            'is_email_compatible' => 'true',
            'placeholder_image' => 'Magento_Cms::images/widget_page_link.png',
        ];
        $this->widgetModelMock->expects(
            $this->once()
        )->method(
            'getWidgetByClassType'
        )->will(
            $this->returnValue($widget)
        );
        $this->viewFileSystemMock->expects($this->once())->method('getFilename')->will($this->returnValue(''));
        $expectedContainers = [];
        $this->assertEquals($expectedContainers, $this->model->getWidgetSupportedContainers());
    }

    public function testGetWidgetSupportedTemplatesByContainers()
    {
        $expectedConfigFile = __DIR__ . '/../_files/mappedConfigArray1.php';
        $widget = include $expectedConfigFile;
        $this->widgetModelMock->expects(
            $this->once()
        )->method(
            'getWidgetByClassType'
        )->will(
            $this->returnValue($widget)
        );
        $this->viewFileSystemMock->expects($this->once())->method('getFilename')->will($this->returnValue(''));
        $expectedTemplates = [
            ['value' => 'product/widget/link/link_block.phtml', 'label' => 'Product Link Block Template'],
            ['value' => 'product/widget/link/link_inline.phtml', 'label' => 'Product Link Inline Template'],
        ];
        $this->assertEquals($expectedTemplates, $this->model->getWidgetSupportedTemplatesByContainer('left'));
    }

    public function testGetWidgetSupportedTemplatesByContainers2()
    {
        $expectedConfigFile = __DIR__ . '/../_files/mappedConfigArray1.php';
        $widget = include $expectedConfigFile;
        $this->widgetModelMock->expects(
            $this->once()
        )->method(
            'getWidgetByClassType'
        )->will(
            $this->returnValue($widget)
        );
        $this->viewFileSystemMock->expects($this->once())->method('getFilename')->will($this->returnValue(''));
        $expectedTemplates = [
            ['value' => 'product/widget/link/link_block.phtml', 'label' => 'Product Link Block Template'],
        ];
        $this->assertEquals($expectedTemplates, $this->model->getWidgetSupportedTemplatesByContainer('content'));
    }

    public function testGetWidgetSupportedTemplatesByContainersNoSupportedContainersSpecified()
    {
        $widget = [
            '@' => ['type' => 'Magento\Cms\Block\Widget\Page\Link', 'module' => 'Magento_Cms'],
            'name' => 'CMS Page Link',
            'description' => 'Link to a CMS Page',
            'is_email_compatible' => 'true',
            'placeholder_image' => 'Magento_Cms::images/widget_page_link.png',
            'parameters' => [
                'template' => [
                    'values' => [
                        'default' => ['value' => 'product/widget/link/link_block.phtml', 'label' => 'Template'],
                    ],
                    'type' => 'select',
                    'visible' => 'true',
                    'label' => 'Template',
                    'value' => 'product/widget/link/link_block.phtml',
                ],
            ],
        ];
        $this->widgetModelMock->expects(
            $this->once()
        )->method(
            'getWidgetByClassType'
        )->will(
            $this->returnValue($widget)
        );
        $this->viewFileSystemMock->expects($this->once())->method('getFilename')->will($this->returnValue(''));
        $expectedContainers = [
            'default' => ['value' => 'product/widget/link/link_block.phtml', 'label' => 'Template'],
        ];
        $this->assertEquals($expectedContainers, $this->model->getWidgetSupportedTemplatesByContainer('content'));
    }

    public function testGetWidgetSupportedTemplatesByContainersUnknownContainer()
    {
        $expectedConfigFile = __DIR__ . '/../_files/mappedConfigArray1.php';
        $widget = include $expectedConfigFile;
        $this->widgetModelMock->expects(
            $this->once()
        )->method(
            'getWidgetByClassType'
        )->will(
            $this->returnValue($widget)
        );
        $this->viewFileSystemMock->expects($this->once())->method('getFilename')->will($this->returnValue(''));
        $expectedTemplates = [];
        $this->assertEquals($expectedTemplates, $this->model->getWidgetSupportedTemplatesByContainer('unknown'));
    }

    /**
     * Test beforeSave when widget parameters is not array.
     *
     * @dataProvider beforeSaveWhenWidgetParametersIsNotArrayDataProvider
     * @param $widgetParameters
     */
    public function testBeforeSaveWhenWidgetParametersIsNotArray($widgetParameters)
    {
        $getDataValueMap = [
            ['page_groups', null, false],
            ['store_ids', null, null],
            ['widget_parameters', null, $widgetParameters],
        ];

        $this->model->expects($this->atLeastOnce())
            ->method('getData')
            ->will($this->returnValueMap($getDataValueMap));

        $this->model->expects($this->atLeastOnce())
            ->method('setData')
            ->will($this->returnValueMap([
                ['widget_parameters', [], true],
            ]));

        $this->loggerInterfaceMock->expects($this->once())->method('error');
        $this->model->beforeSave();
    }

    /**
     * @dataProvider getWidgetParametersDataProvider
     * @param $widgetParameters
     * @param $expectedResult
     */
    public function testGetWidgetParameters($widgetParameters, $expectedResult)
    {
        $this->model->expects($this->atLeastOnce())
            ->method('getData')
            ->with($this->equalTo('widget_parameters'))
            ->will($this->returnValue($widgetParameters));
        $this->assertEquals($expectedResult, $this->model->getWidgetParameters());
    }

    /**
     * @return array
     */
    public function beforeSaveWhenWidgetParametersIsNotArrayDataProvider()
    {
        return [
            [null],
            [''],
            ['widget_parameters'],
            [new \stdClass()],
        ];
    }

    /**
     * @return array
     */
    public function getWidgetParametersDataProvider()
    {
        $widgetParameters = [
            'display_mode' => 'fixed',
            'types' => [],
            'rotate' => '',
        ];
        return [
            [[], []],
            ['', []],
            [serialize($widgetParameters), []],
            [null, []],
            [new \stdClass(), []],
            [$widgetParameters, $widgetParameters],
        ];
    }
}
