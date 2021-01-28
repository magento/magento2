<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Block\System\Config;

class EditTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Config\Block\System\Config\Edit
     */
    protected $_object;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_systemConfigMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_requestMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_layoutMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_urlModelMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_sectionMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_jsonMock;

    protected function setUp(): void
    {
        $this->_systemConfigMock = $this->createMock(\Magento\Config\Model\Config\Structure::class);

        $this->_requestMock = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->_requestMock->expects(
            $this->any()
        )->method(
            'getParam'
        )->with(
            'section'
        )->willReturn(
            'test_section'
        );

        $this->_layoutMock = $this->createMock(\Magento\Framework\View\Layout::class);

        $this->_urlModelMock = $this->createMock(\Magento\Backend\Model\Url::class);

        $this->_sectionMock = $this->createMock(\Magento\Config\Model\Config\Structure\Element\Section::class);
        $this->_systemConfigMock->expects(
            $this->any()
        )->method(
            'getElement'
        )->with(
            'test_section'
        )->willReturn(
            $this->_sectionMock
        );

        $this->_jsonMock = $this->createMock(\Magento\Framework\Serialize\Serializer\Json::class);

        $data = [
            'data' => ['systemConfig' => $this->_systemConfigMock],
            'request' => $this->_requestMock,
            'layout' => $this->_layoutMock,
            'urlBuilder' => $this->_urlModelMock,
            'configStructure' => $this->_systemConfigMock,
            'jsonSerializer' => $this->_jsonMock,
        ];

        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_object = $helper->getObject(\Magento\Config\Block\System\Config\Edit::class, $data);
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
        )->willReturn(
            'test_child_name'
        );

        $this->_layoutMock->expects(
            $this->once()
        )->method(
            'renderElement'
        )->with(
            'test_child_name'
        )->willReturn(
            'element_html_code'
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
        )->willReturnArgument(
            0
        );

        $this->assertEquals($expectedUrl, $this->_object->getSaveUrl());
    }

    public function testPrepareLayout()
    {
        $expectedHeader = 'Test Header';
        $expectedLabel  = 'Test  Label';
        $expectedBlock  = 'Test  Block';

        $blockMock = $this->getMockBuilder(\Magento\Framework\View\Element\Template::class)
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

    /**
     * @param array $requestData
     * @param array $expected
     * @dataProvider getConfigSearchParamsJsonData
     */
    public function testGetConfigSearchParamsJson(array $requestData, array $expected)
    {
        $requestMock = $this->createMock(\Magento\Framework\App\RequestInterface::class);

        $requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap($requestData);
        $this->_jsonMock->expects($this->once())
            ->method('serialize')
            ->with($expected);

        $data = [
            'data' => ['systemConfig' => $this->_systemConfigMock],
            'request' => $requestMock,
            'layout' => $this->_layoutMock,
            'urlBuilder' => $this->_urlModelMock,
            'configStructure' => $this->_systemConfigMock,
            'jsonSerializer' => $this->_jsonMock,
        ];

        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $object = $helper->getObject(\Magento\Config\Block\System\Config\Edit::class, $data);

        $object->getConfigSearchParamsJson();
    }

    /**
     * @return array
     */
    public function getConfigSearchParamsJsonData()
    {
        return [
            [
                [
                    ['section', null, null],
                    ['group', null,  null],
                    ['field', null,  null],
                ],
                [],
            ],
            [
                [
                    ['section', null, 'section_code'],
                    ['group', null,  null],
                    ['field', null,  null],
                ],
                [
                    'section' => 'section_code',
                ],
            ],
            [
                [
                    ['section', null, 'section_code'],
                    ['group', null,  'group_code'],
                    ['field', null,  null],
                ],
                [
                    'section' => 'section_code',
                    'group' => 'group_code',
                ],
            ],
            [
                [
                    ['section', null, 'section_code'],
                    ['group', null,  'group_code'],
                    ['field', null,  'field_code'],
                ],
                [
                    'section' => 'section_code',
                    'group' => 'group_code',
                    'field' => 'field_code',
                ],
            ],
        ];
    }
}
