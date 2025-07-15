<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Integration\Test\Unit\Block\Adminhtml\Widget\Grid\Column\Renderer;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Framework\Escaper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Integration\Block\Adminhtml\Widget\Grid\Column\Renderer\Name;
use Magento\Integration\Model\Integration;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NameTest extends TestCase
{
    /**
     * @var Context|MockObject
     */
    protected $contextMock;

    /**
     * @var Escaper|MockObject
     */
    protected $escaperMock;

    /**
     * @var UrlInterface|MockObject
     */
    protected $urlBuilderMock;

    /**
     * @var ObjectManager
     */
    protected $objectManagerHelper;

    /**
     * @var Name
     */
    protected $nameRenderer;

    protected function setUp(): void
    {
        $this->escaperMock = $this->createMock(Escaper::class);
        $this->escaperMock->expects($this->any())->method('escapeHtml')->willReturnArgument(0);
        $this->urlBuilderMock = $this->getMockForAbstractClass(UrlInterface::class);
        $this->urlBuilderMock->expects($this->any())->method('getUrl')->willReturn('http://magento.loc/linkurl');
        $this->contextMock = $this->createPartialMock(
            Context::class,
            ['getEscaper', 'getUrlBuilder']
        );
        $this->contextMock->expects($this->any())->method('getEscaper')->willReturn($this->escaperMock);
        $this->contextMock->expects($this->any())
            ->method('getUrlBuilder')
            ->willReturn($this->urlBuilderMock);

        $this->objectManagerHelper = new ObjectManager($this);
        $this->nameRenderer = $this->objectManagerHelper->getObject(
            Name::class,
            ['context' => $this->contextMock]
        );
    }

    /**
     * Test the basic render action.
     * @dataProvider endpointDataProvider
     */
    public function testRender($endpoint, $name, $expectedResult)
    {
        $column = $this->getMockBuilder(Column::class)
            ->disableOriginalConstructor()
            ->addMethods(['getIndex', 'getEditable', 'getGetter'])
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

        $integrationMock = $this->getMockBuilder(Integration::class)
            ->disableOriginalConstructor()
            ->addMethods(['getName', 'getEndpoint', 'getIdentityLinkUrl'])
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
    public static function endpointDataProvider()
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
