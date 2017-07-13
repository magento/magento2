<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Test\Unit\Block\Adminhtml\Widget\Grid\Column\Renderer;

class NameTest extends \PHPUnit\Framework\TestCase
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
        $this->escaperMock = $this->createMock(\Magento\Framework\Escaper::class);
        $this->escaperMock->expects($this->any())->method('escapeHtml')->willReturnArgument(0);
        $this->urlBuilderMock = $this->createMock(\Magento\Framework\UrlInterface::class);
        $this->urlBuilderMock->expects($this->any())->method('getUrl')->willReturn('http://magento.loc/linkurl');
        $this->contextMock = $this->createPartialMock(
            \Magento\Backend\Block\Context::class,
            ['getEscaper', 'getUrlBuilder']
        );
        $this->contextMock->expects($this->any())->method('getEscaper')->will($this->returnValue($this->escaperMock));
        $this->contextMock->expects($this->any())
            ->method('getUrlBuilder')
            ->will($this->returnValue($this->urlBuilderMock));

        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->nameRenderer = $this->objectManagerHelper->getObject(
            \Magento\Integration\Block\Adminhtml\Widget\Grid\Column\Renderer\Name::class,
            ['context' => $this->contextMock]
        );
    }

    /**
     * Test the basic render action.
     * @dataProvider endpointDataProvider
     */
    public function testRender($endpoint, $name, $expectedResult)
    {
        $column = $this->getMockBuilder(\Magento\Backend\Block\Widget\Grid\Column::class)
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

        $integrationMock = $this->getMockBuilder(\Magento\Integration\Model\Integration::class)
            ->disableOriginalConstructor()
            ->setMethods(['getName', 'getEndpoint', 'getIdentityLinkUrl'])
            ->getMock();
        $integrationMock->expects($this->any())->method('getName')->willReturn($name);
        $integrationMock->expects($this->any())->method('getEndpoint')->willReturn($endpoint);
        $integrationMock->expects($this->any())->method('getIdentityLinkUrl')->willReturn($endpoint);
        $actualResult = $this->nameRenderer->render($integrationMock);
        $this->assertEquals($expectedResult, $actualResult);
    }

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
                'Custom Integration<span class="security-notice"><span>Integration not secure</span></span>'
            ]
        ];
    }
}
