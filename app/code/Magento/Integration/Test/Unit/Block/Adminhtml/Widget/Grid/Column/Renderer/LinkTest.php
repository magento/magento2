<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Test\Unit\Block\Adminhtml\Widget\Grid\Column\Renderer;

class LinkTest extends \PHPUnit_Framework_TestCase
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
     * @var \Magento\Integration\Block\Adminhtml\Widget\Grid\Column\Renderer\Link
     */
    protected $linkRenderer;

    protected function setUp()
    {
        $this->escaperMock = $this->getMock('Magento\Framework\Escaper', [], [], '', false);
        $this->escaperMock->expects($this->any())->method('escapeHtml')->willReturnArgument(0);
        $this->urlBuilderMock = $this->getMock('Magento\Framework\UrlInterface', [], [], '', false);
        $this->urlBuilderMock->expects($this->once())->method('getUrl')->willReturn('http://magento.loc/linkurl');
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
        $this->linkRenderer = $this->objectManagerHelper->getObject(
            'Magento\Integration\Block\Adminhtml\Widget\Grid\Column\Renderer\Link',
            ['context' => $this->contextMock]
        );
    }

    /**
     * Test the basic render action.
     */
    public function testRender()
    {
        $expectedResult = '<a href="http://magento.loc/linkurl" title="Link Caption">Link Caption</a>';
        $column = $this->getMockBuilder('Magento\Backend\Block\Widget\Grid\Column')
            ->disableOriginalConstructor()
            ->setMethods(['getCaption', 'getId'])
            ->getMock();
        $column->expects($this->any())
            ->method('getCaption')
            ->will($this->returnValue('Link Caption'));
        $column->expects($this->any())
            ->method('getId')
            ->willReturn('1');
        $this->linkRenderer->setColumn($column);
        $object = new \Magento\Framework\DataObject(['id' => '1']);
        $actualResult = $this->linkRenderer->render($object);
        $this->assertEquals($expectedResult, $actualResult);
    }
}
