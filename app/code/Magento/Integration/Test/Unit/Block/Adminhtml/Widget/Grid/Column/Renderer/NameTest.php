<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Integration\Test\Unit\Block\Adminhtml\Widget\Grid\Column\Renderer;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Framework\Escaper;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Integration\Block\Adminhtml\Widget\Grid\Column\Renderer\Name;
use Magento\Integration\Model\Integration;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NameTest extends TestCase
{
    use MockCreationTrait;
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
        $this->urlBuilderMock = $this->createMock(UrlInterface::class);
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
     *
     * @param string $endpoint
     * @param string $name
     * @param string $expectedResult
     */
    #[DataProvider('endpointDataProvider')]
    public function testRender(string $endpoint, string $name, string $expectedResult): void
    {
        $column = $this->createPartialMockWithReflection(
            Column::class,
            ['getIndex', 'getEditable', 'getGetter']
        );
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

        $integrationMock = $this->createPartialMockWithReflection(
            Integration::class,
            ['getName', 'getEndpoint', 'getIdentityLinkUrl']
        );
        $integrationMock->expects($this->any())->method('getName')->willReturn($name);
        $integrationMock->expects($this->any())->method('getEndpoint')->willReturn($endpoint);
        $integrationMock->expects($this->any())->method('getIdentityLinkUrl')->willReturn($endpoint);
        $actualResult = $this->nameRenderer->render($integrationMock);
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @return array<int, array<int, string>>
     */
    public static function endpointDataProvider(): array
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
