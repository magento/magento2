<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Block\System\Config;

class EditTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Config\Block\System\Config\Edit
     */
    protected $_object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_systemConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_layoutMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_urlModelMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_sectionMock;

    protected function setUp()
    {
        $this->_systemConfigMock = $this->getMock(
            'Magento\Config\Model\Config\Structure',
            [],
            [],
            '',
            false,
            false
        );

        $this->_requestMock = $this->getMock(
            'Magento\Framework\App\RequestInterface',
            [],
            [],
            '',
            false,
            false
        );
        $this->_requestMock->expects(
            $this->any()
        )->method(
            'getParam'
        )->with(
            'section'
        )->will(
            $this->returnValue('test_section')
        );

        $this->_layoutMock = $this->getMock('Magento\Framework\View\Layout', [], [], '', false, false);

        $this->_urlModelMock = $this->getMock('Magento\Backend\Model\Url', [], [], '', false, false);

        $this->_sectionMock = $this->getMock(
            'Magento\Config\Model\Config\Structure\Element\Section',
            [],
            [],
            '',
            false
        );
        $this->_systemConfigMock->expects(
            $this->any()
        )->method(
            'getElement'
        )->with(
            'test_section'
        )->will(
            $this->returnValue($this->_sectionMock)
        );

        $data = [
            'data' => ['systemConfig' => $this->_systemConfigMock],
            'request' => $this->_requestMock,
            'layout' => $this->_layoutMock,
            'urlBuilder' => $this->_urlModelMock,
            'configStructure' => $this->_systemConfigMock,
        ];

        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_object = $helper->getObject('Magento\Config\Block\System\Config\Edit', $data);
    }

    public function testGetSaveButtonHtml()
    {
        $expected = 'element_html_code';

        $this->_layoutMock->expects(
            $this->once()
        )->method(
            'getChildName'
        )->with(
            null,
            'save_button'
        )->will(
            $this->returnValue('test_child_name')
        );

        $this->_layoutMock->expects(
            $this->once()
        )->method(
            'renderElement'
        )->with(
            'test_child_name'
        )->will(
            $this->returnValue('element_html_code')
        );

        $this->assertEquals($expected, $this->_object->getSaveButtonHtml());
    }

    public function testGetSaveUrl()
    {
        $expectedUrl = '*/system_config/save';
        $expectedParams = ['_current' => true];

        $this->_urlModelMock->expects(
            $this->once()
        )->method(
            'getUrl'
        )->with(
            $expectedUrl,
            $expectedParams
        )->will(
            $this->returnArgument(0)
        );

        $this->assertEquals($expectedUrl, $this->_object->getSaveUrl());
    }

    public function testPrepareLayout()
    {
        $expectedHeader = 'Test Header';
        $expectedLabel  = 'Test  Label';
        $expectedBlock  = 'Test  Block';

        $blockMock = $this->getMockBuilder('Magento\Framework\View\Element\Template')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_sectionMock->expects($this->once())
            ->method('getFrontendModel')
            ->willReturn($expectedBlock);
        $this->_sectionMock->expects($this->once())
            ->method('getLabel')
            ->willReturn($expectedLabel);
        $this->_sectionMock->expects($this->once())
            ->method('getHeaderCss')
            ->willReturn($expectedHeader);
        $this->_layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('page.actions.toolbar')
            ->willReturn($blockMock);
        $this->_layoutMock->expects($this->once())
            ->method('createBlock')
            ->with($expectedBlock)
            ->willReturn($blockMock);
        $blockMock->expects($this->once())
            ->method('getNameInLayout')
            ->willReturn($expectedBlock);
        $this->_layoutMock->expects($this->once())
            ->method('setChild')
            ->with($expectedBlock, $expectedBlock, 'form')
            ->willReturn($this->_layoutMock);

        $this->_object->setNameInLayout($expectedBlock);
        $this->_object->setLayout($this->_layoutMock);
    }
}
