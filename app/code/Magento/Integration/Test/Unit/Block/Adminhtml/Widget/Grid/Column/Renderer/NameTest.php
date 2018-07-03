<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Test\Unit\Block\Adminhtml\Widget\Grid\Column\Renderer;

class NameTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Block\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Framework\Escaper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $escaperMock;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilderMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Integration\Block\Adminhtml\Widget\Grid\Column\Renderer\Name
     */
    protected $nameRenderer;

    protected function setUp()
    {
        $this->escaperMock = $this->getMock('Magento\Framework\Escaper', [], [], '', false);
        $this->escaperMock->expects($this->any())->method('escapeHtml')->willReturnArgument(0);
        $this->urlBuilderMock = $this->getMock('Magento\Framework\UrlInterface', [], [], '', false);
        $this->urlBuilderMock->expects($this->any())->method('getUrl')->willReturn('http://magento.loc/linkurl');
        $this->contextMock = $this->getMock(
            'Magento\Backend\Block\Context',
            ['getEscaper', 'getUrlBuilder'],
            [],
            '',
            false
        );
        $this->contextMock->expects($this->any())->method('getEscaper')->will($this->returnValue($this->escaperMock));
        $this->contextMock->expects($this->any())
            ->method('getUrlBuilder')
            ->will($this->returnValue($this->urlBuilderMock));

        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->nameRenderer = $this->objectManagerHelper->getObject(
            'Magento\Integration\Block\Adminhtml\Widget\Grid\Column\Renderer\Name',
            ['context' => $this->contextMock]
        );
    }

    /**
     * Test the basic render action.
     * @dataProvider endpointDataProvider
     */
    public function testRender($endpoint, $name, $expectedResult)
    {
        $column = $this->getMockBuilder('Magento\Backend\Block\Widget\Grid\Column')
            ->disableOriginalConstructor()
            ->setMethods(['getIndex', 'getEditable', 'getGetter'])
            ->getMock();
        $column->expects($this->any())
            ->method('getIndex')
            ->willReturn('name');
        $column->expects($this->any())
            ->method('getEditable')
            ->willReturn(false);
        $column->expects($this->any())
            ->method('getGetter')
            ->willReturn('getName');
        $this->nameRenderer->setColumn($column);

        $integrationMock = $this->getMockBuilder('Magento\Integration\Model\Integration')
            ->disableOriginalConstructor()
            ->setMethods(['getName', 'getEndpoint', 'getIdentityLinkUrl'])
            ->getMock();
        $integrationMock->expects($this->any())->method('getName')->willReturn($name);
        $integrationMock->expects($this->any())->method('getEndpoint')->willReturn($endpoint);
        $integrationMock->expects($this->any())->method('getIdentityLinkUrl')->willReturn($endpoint);
        $actualResult = $this->nameRenderer->render($integrationMock);
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @return array
     */
    public function endpointDataProvider()
    {
        return [
            [
                'https://myurl',
                'Custom Integration',
                'Custom Integration'
            ],
            [
                'http://myurl',
                'Custom Integration',
                'Custom Integration<span class="icon-error"><span>Integration not secure</span></span>'
            ]
        ];
    }
}
