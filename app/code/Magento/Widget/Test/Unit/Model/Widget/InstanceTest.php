<?php
/**
 * \Magento\Widget\Model\Widget\Instance
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Test\Unit\Model\Widget;

class InstanceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Widget\Model\Config\Data|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_widgetModelMock;

    /**
     * @var \Magento\Framework\View\FileSystem|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_viewFileSystemMock;

    /** @var  \Magento\Widget\Model\NamespaceResolver |PHPUnit_Framework_MockObject_MockObject */
    protected $_namespaceResolver;

    /**
     * @var \Magento\Widget\Model\Widget\Instance
     */
    protected $_model;

    /** @var  \Magento\Widget\Model\Config\Reader */
    protected $_readerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cacheTypesListMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_directoryMock;

    public function setUp()
    {
        $this->_widgetModelMock = $this->getMockBuilder(
            'Magento\Widget\Model\Widget'
        )->disableOriginalConstructor()->getMock();
        $this->_viewFileSystemMock = $this->getMockBuilder(
            'Magento\Framework\View\FileSystem'
        )->disableOriginalConstructor()->getMock();
        $this->_namespaceResolver = $this->getMockBuilder(
            '\Magento\Widget\Model\NamespaceResolver'
        )->disableOriginalConstructor()->getMock();
        $this->_cacheTypesListMock = $this->getMock('Magento\Framework\App\Cache\TypeListInterface');
        $this->_readerMock = $this->getMockBuilder(
            'Magento\Widget\Model\Config\Reader'
        )->disableOriginalConstructor()->getMock();

        $filesystemMock = $this->getMock('\Magento\Framework\Filesystem', [], [], '', false);
        $this->_directoryMock = $this->getMock(
            '\Magento\Framework\Filesystem\Directory\Read',
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
            $this->returnValue($this->_directoryMock)
        );
        $this->_directoryMock->expects($this->any())->method('isReadable')->will($this->returnArgument(0));
        $this->_directoryMock->expects($this->any())->method('getRelativePath')->will($this->returnArgument(0));
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $args = $objectManagerHelper->getConstructArguments(
            'Magento\Widget\Model\Widget\Instance',
            [
                'filesystem' => $filesystemMock,
                'viewFileSystem' => $this->_viewFileSystemMock,
                'cacheTypeList' => $this->_cacheTypesListMock,
                'reader' => $this->_readerMock,
                'widgetModel' => $this->_widgetModelMock,
                'namespaceResolver' => $this->_namespaceResolver
            ]
        );
        /** @var \Magento\Widget\Model\Widget\Instance _model */
        $this->_model = $this->getMock('Magento\Widget\Model\Widget\Instance', ['_construct'], $args, '', true);
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
        $this->_widgetModelMock->expects(
            $this->once()
        )->method(
            'getWidgetByClassType'
        )->will(
            $this->returnValue($widget)
        );
        $xmlFile = __DIR__ . '/../_files/widget.xml';
        $this->_viewFileSystemMock->expects($this->once())->method('getFilename')->will($this->returnValue($xmlFile));
        $themeConfigFile = __DIR__ . '/../_files/mappedConfigArrayAll.php';
        $themeConfig = include $themeConfigFile;
        $this->_readerMock->expects(
            $this->once()
        )->method(
            'readFile'
        )->with(
            $this->equalTo($xmlFile)
        )->will(
            $this->returnValue($themeConfig)
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
        )->will(
            $this->returnValue($widget)
        );
        $this->_viewFileSystemMock->expects($this->once())->method('getFilename')->will($this->returnValue(''));
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
        $this->_widgetModelMock->expects(
            $this->once()
        )->method(
            'getWidgetByClassType'
        )->will(
            $this->returnValue($widget)
        );
        $this->_viewFileSystemMock->expects($this->once())->method('getFilename')->will($this->returnValue(''));
        $expectedTemplates = [
            'default' => ['value' => 'product/widget/link/link_block.phtml', 'label' => 'Template'],
        ];
        $this->assertEquals($expectedTemplates, $this->_model->getWidgetTemplates());
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
        $this->_widgetModelMock->expects(
            $this->once()
        )->method(
            'getWidgetByClassType'
        )->will(
            $this->returnValue($widget)
        );
        $this->_viewFileSystemMock->expects($this->once())->method('getFilename')->will($this->returnValue(''));
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
        )->will(
            $this->returnValue($widget)
        );
        $this->_viewFileSystemMock->expects($this->once())->method('getFilename')->will($this->returnValue(''));
        $expectedContainers = ['left', 'content'];
        $this->assertEquals($expectedContainers, $this->_model->getWidgetSupportedContainers());
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
        $this->_widgetModelMock->expects(
            $this->once()
        )->method(
            'getWidgetByClassType'
        )->will(
            $this->returnValue($widget)
        );
        $this->_viewFileSystemMock->expects($this->once())->method('getFilename')->will($this->returnValue(''));
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
        )->will(
            $this->returnValue($widget)
        );
        $this->_viewFileSystemMock->expects($this->once())->method('getFilename')->will($this->returnValue(''));
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
        )->will(
            $this->returnValue($widget)
        );
        $this->_viewFileSystemMock->expects($this->once())->method('getFilename')->will($this->returnValue(''));
        $expectedTemplates = [
            ['value' => 'product/widget/link/link_block.phtml', 'label' => 'Product Link Block Template'],
        ];
        $this->assertEquals($expectedTemplates, $this->_model->getWidgetSupportedTemplatesByContainer('content'));
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
        $this->_widgetModelMock->expects(
            $this->once()
        )->method(
            'getWidgetByClassType'
        )->will(
            $this->returnValue($widget)
        );
        $this->_viewFileSystemMock->expects($this->once())->method('getFilename')->will($this->returnValue(''));
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
        )->will(
            $this->returnValue($widget)
        );
        $this->_viewFileSystemMock->expects($this->once())->method('getFilename')->will($this->returnValue(''));
        $expectedTemplates = [];
        $this->assertEquals($expectedTemplates, $this->_model->getWidgetSupportedTemplatesByContainer('unknown'));
    }
}
