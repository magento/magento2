<?php
/**
 * \Magento\Widget\Model\Widget\Instance
 *
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
namespace Magento\Widget\Model\Widget;

class InstanceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Widget\Model\Config\Data|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_widgetModelMock;

    /**
     * @var \Magento\Core\Model\View\FileSystem|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_viewFileSystemMock;

    /** @var  \Magento\Core\Model\Config|PHPUnit_Framework_MockObject_MockObject */
    protected $_coreConfigMock;

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

    public function setUp()
    {
        $this->_widgetModelMock = $this->getMockBuilder('Magento\Widget\Model\Widget')
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock = $this->getMockBuilder('Magento\Core\Model\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_viewFileSystemMock = $this->getMockBuilder('Magento\Core\Model\View\FileSystem')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_coreConfigMock = $this->getMockBuilder('Magento\Core\Model\Config')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_cacheTypesListMock = $this->getMock('Magento\Core\Model\Cache\TypeListInterface');
        $this->_readerMock = $this->getMockBuilder('Magento\Widget\Model\Config\Reader')
            ->disableOriginalConstructor()
            ->getMock();
        $registryMock = $this->getMockBuilder('Magento\Core\Model\Registry')
            ->disableOriginalConstructor()
            ->getMock();
        $coreData = $this->getMockBuilder('Magento\Core\Helper\Data')
            ->disableOriginalConstructor()
            ->getMock();
        $widgetData = $this->getMockBuilder('Magento\Widget\Helper\Data')
            ->disableOriginalConstructor()
            ->getMock();
        $productType = $this->getMockBuilder('Magento\Catalog\Model\Product\Type')
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \Magento\Widget\Model\Widget\Instance _model */
        $this->_model = $this->getMock(
            'Magento\Widget\Model\Widget\Instance',
            array('_construct'),
            array(
                $widgetData, $coreData, $contextMock, $registryMock, $this->_viewFileSystemMock,
                $this->_cacheTypesListMock, $productType, $this->_readerMock, $this->_widgetModelMock,
                $this->_coreConfigMock
            ),
            '',
            true
        );
    }

    public function testGetWidgetConfigAsArray()
    {
        $widget = array(
            '@' => array(
                'type' => 'Magento\Cms\Block\Widget\Page\Link',
                'module' => 'Magento_Cms',
            ),
            'name' => 'CMS Page Link',
            'description' => 'Link to a CMS Page',
            'is_email_compatible' => 'true',
            'placeholder_image' => 'Magento_Cms::images/widget_page_link.gif',
            'parameters' => array(
                'page_id' => array(
                    '@' => array(
                        'type' => 'complex',
                    ),
                    'type' => 'label',
                    'helper_block' => array(
                        'type' => 'Magento\Adminhtml\Block\Cms\Page\Widget\Chooser',
                        'data' => array(
                            'button' => array(
                                'open' => 'Select Page...',
                            ),
                        ),
                    ),
                    'visible' => 'true',
                    'required' => 'true',
                    'sort_order' => '10',
                    'label' => 'CMS Page',
                ),
            ),
        );
        $this->_widgetModelMock->expects($this->once())->method('getWidgetByClassType')
            ->will($this->returnValue($widget));
        $xmlFile = __DIR__ . '/../_files/widget.xml';
        $this->_viewFileSystemMock->expects($this->once())->method('getFilename')
            ->will($this->returnValue($xmlFile));
        $themeConfigFile = __DIR__ . '/../_files/mappedConfigArrayAll.php';
        $themeConfig = include $themeConfigFile;
        $this->_readerMock->expects($this->once())->method('readFile')->with($this->equalTo($xmlFile))
            ->will($this->returnValue($themeConfig));

        $result = $this->_model->getWidgetConfigAsArray();

        $expectedConfigFile = __DIR__ . '/../_files/mappedConfigArray1.php';
        $expectedConfig = include $expectedConfigFile;
        $this->assertEquals($expectedConfig, $result);
    }

    public function testGetWidgetTemplates()
    {
        $expectedConfigFile = __DIR__ . '/../_files/mappedConfigArray1.php';
        $widget = include $expectedConfigFile;
        $this->_widgetModelMock->expects($this->once())->method('getWidgetByClassType')
            ->will($this->returnValue($widget));
        $this->_viewFileSystemMock->expects($this->once())->method('getFilename')
            ->will($this->returnValue(''));
        $expectedTemplates = array(
            'default' => array(
                'value' => 'product/widget/link/link_block.phtml',
                'label' => 'Product Link Block Template',
            ),
            'link_inline' => array(
                'value' => 'product/widget/link/link_inline.phtml',
                'label' => 'Product Link Inline Template',
            )
        );
        $this->assertEquals($expectedTemplates, $this->_model->getWidgetTemplates());
    }

    public function testGetWidgetTemplatesValueOnly()
    {
        $widget = array(
            '@' => array(
                'type' => 'Magento\Cms\Block\Widget\Page\Link',
                'module' => 'Magento_Cms',
            ),
            'name' => 'CMS Page Link',
            'description' => 'Link to a CMS Page',
            'is_email_compatible' => 'true',
            'placeholder_image' => 'Magento_Cms::images/widget_page_link.gif',
            'parameters' => array(
                'template' => array(
                    'values' => array(
                        'default' => array(
                            'value' => 'product/widget/link/link_block.phtml',
                            'label' => 'Template'
                        )
                    ),
                    'type' => 'select',
                    'visible' => 'true',
                    'label' => 'Template',
                    'value' => 'product/widget/link/link_block.phtml',
                ),
            ),
        );
        $this->_widgetModelMock->expects($this->once())->method('getWidgetByClassType')
            ->will($this->returnValue($widget));
        $this->_viewFileSystemMock->expects($this->once())->method('getFilename')
            ->will($this->returnValue(''));
        $expectedTemplates = array(
            'default' => array(
                'value' => 'product/widget/link/link_block.phtml',
                'label' => 'Template',
            ),
        );
        $this->assertEquals($expectedTemplates, $this->_model->getWidgetTemplates());
    }

    public function testGetWidgetTemplatesNoTemplate()
    {
        $widget = array(
            '@' => array(
                'type' => 'Magento\Cms\Block\Widget\Page\Link',
                'module' => 'Magento_Cms',
            ),
            'name' => 'CMS Page Link',
            'description' => 'Link to a CMS Page',
            'is_email_compatible' => 'true',
            'placeholder_image' => 'Magento_Cms::images/widget_page_link.gif',
            'parameters' => array(
            ),
        );
        $this->_widgetModelMock->expects($this->once())->method('getWidgetByClassType')
            ->will($this->returnValue($widget));
        $this->_viewFileSystemMock->expects($this->once())->method('getFilename')
            ->will($this->returnValue(''));
        $expectedTemplates = array();
        $this->assertEquals($expectedTemplates, $this->_model->getWidgetTemplates());
    }

    public function testGetWidgetSupportedContainers()
    {
        $expectedConfigFile = __DIR__ . '/../_files/mappedConfigArray1.php';
        $widget = include $expectedConfigFile;
        $this->_widgetModelMock->expects($this->once())->method('getWidgetByClassType')
            ->will($this->returnValue($widget));
        $this->_viewFileSystemMock->expects($this->once())->method('getFilename')
            ->will($this->returnValue(''));
        $expectedContainers = array('left', 'content');
        $this->assertEquals($expectedContainers, $this->_model->getWidgetSupportedContainers());
    }

    public function testGetWidgetSupportedContainersNoContainer()
    {
        $widget = array(
            '@' => array(
                'type' => 'Magento\Cms\Block\Widget\Page\Link',
                'module' => 'Magento_Cms',
            ),
            'name' => 'CMS Page Link',
            'description' => 'Link to a CMS Page',
            'is_email_compatible' => 'true',
            'placeholder_image' => 'Magento_Cms::images/widget_page_link.gif',
        );
        $this->_widgetModelMock->expects($this->once())->method('getWidgetByClassType')
            ->will($this->returnValue($widget));
        $this->_viewFileSystemMock->expects($this->once())->method('getFilename')
            ->will($this->returnValue(''));
        $expectedContainers = array();
        $this->assertEquals($expectedContainers, $this->_model->getWidgetSupportedContainers());
    }

    public function testGetWidgetSupportedTemplatesByContainers()
    {
        $expectedConfigFile = __DIR__ . '/../_files/mappedConfigArray1.php';
        $widget = include $expectedConfigFile;
        $this->_widgetModelMock->expects($this->once())->method('getWidgetByClassType')
            ->will($this->returnValue($widget));
        $this->_viewFileSystemMock->expects($this->once())->method('getFilename')
            ->will($this->returnValue(''));
        $expectedTemplates = array(
            array(
                'value' => 'product/widget/link/link_block.phtml',
                'label' => 'Product Link Block Template',
            ),
            array(
                'value' => 'product/widget/link/link_inline.phtml',
                'label' => 'Product Link Inline Template',
            )
        );
        $this->assertEquals($expectedTemplates, $this->_model->getWidgetSupportedTemplatesByContainer('left'));
    }

    public function testGetWidgetSupportedTemplatesByContainers2()
    {
        $expectedConfigFile = __DIR__ . '/../_files/mappedConfigArray1.php';
        $widget = include $expectedConfigFile;
        $this->_widgetModelMock->expects($this->once())->method('getWidgetByClassType')
            ->will($this->returnValue($widget));
        $this->_viewFileSystemMock->expects($this->once())->method('getFilename')
            ->will($this->returnValue(''));
        $expectedTemplates = array(
            array(
                'value' => 'product/widget/link/link_block.phtml',
                'label' => 'Product Link Block Template',
            ),
        );
        $this->assertEquals($expectedTemplates, $this->_model->getWidgetSupportedTemplatesByContainer('content'));
    }

    public function testGetWidgetSupportedTemplatesByContainersNoSupportedContainersSpecified()
    {
        $widget = array(
            '@' => array(
                'type' => 'Magento\Cms\Block\Widget\Page\Link',
                'module' => 'Magento_Cms',
            ),
            'name' => 'CMS Page Link',
            'description' => 'Link to a CMS Page',
            'is_email_compatible' => 'true',
            'placeholder_image' => 'Magento_Cms::images/widget_page_link.gif',
            'parameters' => array(
                'template' => array(
                    'values' => array(
                        'default' => array(
                            'value' => 'product/widget/link/link_block.phtml',
                            'label' => 'Template'
                        )
                    ),
                    'type' => 'select',
                    'visible' => 'true',
                    'label' => 'Template',
                    'value' => 'product/widget/link/link_block.phtml',
                ),
            ),
        );;
        $this->_widgetModelMock->expects($this->once())->method('getWidgetByClassType')
            ->will($this->returnValue($widget));
        $this->_viewFileSystemMock->expects($this->once())->method('getFilename')
            ->will($this->returnValue(''));
        $expectedContainers = array(
            'default' => array(
                'value' => 'product/widget/link/link_block.phtml',
                'label' => 'Template',
            ),
        );
        $this->assertEquals($expectedContainers, $this->_model->getWidgetSupportedTemplatesByContainer('content'));
    }

    public function testGetWidgetSupportedTemplatesByContainersUnknownContainer()
    {
        $expectedConfigFile = __DIR__ . '/../_files/mappedConfigArray1.php';
        $widget = include $expectedConfigFile;
        $this->_widgetModelMock->expects($this->once())->method('getWidgetByClassType')
            ->will($this->returnValue($widget));
        $this->_viewFileSystemMock->expects($this->once())->method('getFilename')
            ->will($this->returnValue(''));
        $expectedTemplates = array();
        $this->assertEquals($expectedTemplates, $this->_model->getWidgetSupportedTemplatesByContainer('unknown'));
    }
}
