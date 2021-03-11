<?php
/**
 * \Magento\Widget\Model\Widget\Instance
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Test\Unit\Model\Widget;

use Magento\Framework\Serialize\Serializer\Json;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InstanceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Widget\Model\Config\Data|PHPUnit\Framework\MockObject\MockObject
     */
    protected $_widgetModelMock;

    /**
     * @var \Magento\Framework\View\FileSystem|PHPUnit\Framework\MockObject\MockObject
     */
    protected $_viewFileSystemMock;

    /** @var  \Magento\Widget\Model\NamespaceResolver |PHPUnit\Framework\MockObject\MockObject */
    protected $_namespaceResolver;

    /**
     * @var \Magento\Widget\Model\Widget\Instance
     */
    protected $_model;

    /** @var  \Magento\Widget\Model\Config\Reader */
    protected $_readerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_cacheTypesListMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_directoryMock;

    /** @var \Magento\Framework\Serialize\Serializer\Json | \PHPUnit\Framework\MockObject\MockObject */
    private $serializer;

    protected function setUp(): void
    {
        $this->_widgetModelMock = $this->getMockBuilder(
            \Magento\Widget\Model\Widget::class
        )->disableOriginalConstructor()->getMock();
        $this->_viewFileSystemMock = $this->getMockBuilder(
            \Magento\Framework\View\FileSystem::class
        )->disableOriginalConstructor()->getMock();
        $this->_namespaceResolver = $this->getMockBuilder(
            \Magento\Widget\Model\NamespaceResolver::class
        )->disableOriginalConstructor()->getMock();
        $this->_cacheTypesListMock = $this->createMock(\Magento\Framework\App\Cache\TypeListInterface::class);
        $this->_readerMock = $this->getMockBuilder(
            \Magento\Widget\Model\Config\Reader::class
        )->disableOriginalConstructor()->getMock();

        $filesystemMock = $this->createMock(\Magento\Framework\Filesystem::class);
        $this->_directoryMock = $this->createMock(\Magento\Framework\Filesystem\Directory\Read::class);
        $filesystemMock->expects(
            $this->any()
        )->method(
            'getDirectoryRead'
        )->willReturn(
            $this->_directoryMock
        );
        $this->_directoryMock->expects($this->any())->method('isReadable')->willReturnArgument(0);
        $this->_directoryMock->expects($this->any())->method('getRelativePath')->willReturnArgument(0);
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->serializer = $this->createMock(Json::class);
        $args = $objectManagerHelper->getConstructArguments(
            \Magento\Widget\Model\Widget\Instance::class,
            [
                'filesystem' => $filesystemMock,
                'viewFileSystem' => $this->_viewFileSystemMock,
                'cacheTypeList' => $this->_cacheTypesListMock,
                'reader' => $this->_readerMock,
                'widgetModel' => $this->_widgetModelMock,
                'namespaceResolver' => $this->_namespaceResolver,
                'serializer' => $this->serializer,
            ]
        );

        /** @var \Magento\Widget\Model\Widget\Instance _model */
        $this->_model = $this->getMockBuilder(\Magento\Widget\Model\Widget\Instance::class)
            ->setMethods(['_construct'])
            ->setConstructorArgs($args)
            ->getMock();
    }

    public function testGetWidgetConfigAsArray()
    {
        $widget = [
            '@' => ['type' => \Magento\Cms\Block\Widget\Page\Link::class, 'module' => 'Magento_Cms'],
            'name' => 'CMS Page Link',
            'description' => 'Link to a CMS Page',
            'is_email_compatible' => 'true',
            'placeholder_image' => 'Magento_Cms::images/widget_page_link.png',
            'parameters' => [
                'page_id' => [
                    '@' => ['type' => 'complex'],
                    'type' => 'label',
                    'helper_block' => [
                        'type' => \Magento\Cms\Block\Adminhtml\Page\Widget\Chooser::class,
                        'data' => ['button' => ['open' => 'Select Page...']],
                    ],
                    'visible' => 'true',
                    'required' => 'true',
                    'sort_order' => '10',
                    'label' => 'CMS Page',
                ],
            ],
        ];
        $this->_widgetModelMock->expects(
            $this->once()
        )->method(
            'getWidgetByClassType'
        )->willReturn(
            $widget
        );
        $xmlFile = __DIR__ . '/../_files/widget.xml';
        $this->_viewFileSystemMock->expects($this->once())->method('getFilename')->willReturn($xmlFile);
        $themeConfigFile = __DIR__ . '/../_files/mappedConfigArrayAll.php';
        $themeConfig = include $themeConfigFile;
        $this->_readerMock->expects(
            $this->once()
        )->method(
            'readFile'
        )->with(
            $this->equalTo($xmlFile)
        )->willReturn(
            $themeConfig
        );

        $result = $this->_model->getWidgetConfigAsArray();

        $expectedConfigFile = __DIR__ . '/../_files/mappedConfigArray1.php';
        $expectedConfig = include $expectedConfigFile;
        $this->assertEquals($expectedConfig, $result);
    }

    public function testGetWidgetTemplates()
    {
        $expectedConfigFile = __DIR__ . '/../_files/mappedConfigArray1.php';
        $widget = include $expectedConfigFile;
        $this->_widgetModelMock->expects(
            $this->once()
        )->method(
            'getWidgetByClassType'
        )->willReturn(
            $widget
        );
        $this->_viewFileSystemMock->expects($this->once())->method('getFilename')->willReturn('');
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
        $this->assertEquals($expectedTemplates, $this->_model->getWidgetTemplates());
    }

    public function testGetWidgetTemplatesValueOnly()
    {
        $widget = [
            '@' => ['type' => \Magento\Cms\Block\Widget\Page\Link::class, 'module' => 'Magento_Cms'],
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
        $this->_widgetModelMock->expects(
            $this->once()
        )->method(
            'getWidgetByClassType'
        )->willReturn(
            $widget
        );
        $this->_viewFileSystemMock->expects($this->once())->method('getFilename')->willReturn('');
        $expectedTemplates = [
            'default' => ['value' => 'product/widget/link/link_block.phtml', 'label' => 'Template'],
        ];
        $this->assertEquals($expectedTemplates, $this->_model->getWidgetTemplates());
    }

    public function testGetWidgetTemplatesNoTemplate()
    {
        $widget = [
            '@' => ['type' => \Magento\Cms\Block\Widget\Page\Link::class, 'module' => 'Magento_Cms'],
            'name' => 'CMS Page Link',
            'description' => 'Link to a CMS Page',
            'is_email_compatible' => 'true',
            'placeholder_image' => 'Magento_Cms::images/widget_page_link.png',
            'parameters' => [],
        ];
        $this->_widgetModelMock->expects(
            $this->once()
        )->method(
            'getWidgetByClassType'
        )->willReturn(
            $widget
        );
        $this->_viewFileSystemMock->expects($this->once())->method('getFilename')->willReturn('');
        $expectedTemplates = [];
        $this->assertEquals($expectedTemplates, $this->_model->getWidgetTemplates());
    }

    public function testGetWidgetSupportedContainers()
    {
        $expectedConfigFile = __DIR__ . '/../_files/mappedConfigArray1.php';
        $widget = include $expectedConfigFile;
        $this->_widgetModelMock->expects(
            $this->once()
        )->method(
            'getWidgetByClassType'
        )->willReturn(
            $widget
        );
        $this->_viewFileSystemMock->expects($this->once())->method('getFilename')->willReturn('');
        $expectedContainers = ['left', 'content'];
        $this->assertEquals($expectedContainers, $this->_model->getWidgetSupportedContainers());
    }

    public function testGetWidgetSupportedContainersNoContainer()
    {
        $widget = [
            '@' => ['type' => \Magento\Cms\Block\Widget\Page\Link::class, 'module' => 'Magento_Cms'],
            'name' => 'CMS Page Link',
            'description' => 'Link to a CMS Page',
            'is_email_compatible' => 'true',
            'placeholder_image' => 'Magento_Cms::images/widget_page_link.png',
        ];
        $this->_widgetModelMock->expects(
            $this->once()
        )->method(
            'getWidgetByClassType'
        )->willReturn(
            $widget
        );
        $this->_viewFileSystemMock->expects($this->once())->method('getFilename')->willReturn('');
        $expectedContainers = [];
        $this->assertEquals($expectedContainers, $this->_model->getWidgetSupportedContainers());
    }

    public function testGetWidgetSupportedTemplatesByContainers()
    {
        $expectedConfigFile = __DIR__ . '/../_files/mappedConfigArray1.php';
        $widget = include $expectedConfigFile;
        $this->_widgetModelMock->expects(
            $this->once()
        )->method(
            'getWidgetByClassType'
        )->willReturn(
            $widget
        );
        $this->_viewFileSystemMock->expects($this->once())->method('getFilename')->willReturn('');
        $expectedTemplates = [
            ['value' => 'product/widget/link/link_block.phtml', 'label' => 'Product Link Block Template'],
            ['value' => 'product/widget/link/link_inline.phtml', 'label' => 'Product Link Inline Template'],
        ];
        $this->assertEquals($expectedTemplates, $this->_model->getWidgetSupportedTemplatesByContainer('left'));
    }

    public function testGetWidgetSupportedTemplatesByContainers2()
    {
        $expectedConfigFile = __DIR__ . '/../_files/mappedConfigArray1.php';
        $widget = include $expectedConfigFile;
        $this->_widgetModelMock->expects(
            $this->once()
        )->method(
            'getWidgetByClassType'
        )->willReturn(
            $widget
        );
        $this->_viewFileSystemMock->expects($this->once())->method('getFilename')->willReturn('');
        $expectedTemplates = [
            ['value' => 'product/widget/link/link_block.phtml', 'label' => 'Product Link Block Template'],
        ];
        $this->assertEquals($expectedTemplates, $this->_model->getWidgetSupportedTemplatesByContainer('content'));
    }

    public function testGetWidgetSupportedTemplatesByContainersNoSupportedContainersSpecified()
    {
        $widget = [
            '@' => ['type' => \Magento\Cms\Block\Widget\Page\Link::class, 'module' => 'Magento_Cms'],
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
        $this->_widgetModelMock->expects(
            $this->once()
        )->method(
            'getWidgetByClassType'
        )->willReturn(
            $widget
        );
        $this->_viewFileSystemMock->expects($this->once())->method('getFilename')->willReturn('');
        $expectedContainers = [
            'default' => ['value' => 'product/widget/link/link_block.phtml', 'label' => 'Template'],
        ];
        $this->assertEquals($expectedContainers, $this->_model->getWidgetSupportedTemplatesByContainer('content'));
    }

    public function testGetWidgetSupportedTemplatesByContainersUnknownContainer()
    {
        $expectedConfigFile = __DIR__ . '/../_files/mappedConfigArray1.php';
        $widget = include $expectedConfigFile;
        $this->_widgetModelMock->expects(
            $this->once()
        )->method(
            'getWidgetByClassType'
        )->willReturn(
            $widget
        );
        $this->_viewFileSystemMock->expects($this->once())->method('getFilename')->willReturn('');
        $expectedTemplates = [];
        $this->assertEquals($expectedTemplates, $this->_model->getWidgetSupportedTemplatesByContainer('unknown'));
    }

    public function testGetWidgetParameters()
    {
        $serializedArray = '{"anchor_text":"232323232323232323","title":"232323232323232","page_id":"2"}';
        $this->serializer->expects($this->once())
            ->method('unserialize')
            ->willReturn(json_decode($serializedArray, true));

        $this->_model->setData('widget_parameters', $serializedArray);
        $this->assertEquals(
            json_decode($serializedArray, true),
            $this->_model->getWidgetParameters()
        );
    }

    public function testBeforeSave()
    {
        $widgetParameters = [
            'anchor_text' => 'Test',
            'title' => 'Test',
            'page_id' => '2'
        ];
        $this->serializer->expects($this->once())
            ->method('serialize')
            ->willReturn(json_encode($widgetParameters));

        $this->_model->setData('widget_parameters', $widgetParameters);
        $this->_model->beforeSave();
    }
}
