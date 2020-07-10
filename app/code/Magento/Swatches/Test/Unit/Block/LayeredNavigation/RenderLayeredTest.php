<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Swatches\Test\Unit\Block\LayeredNavigation;

use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Catalog\Model\Layer\Filter\Item;
use Magento\Catalog\Model\ResourceModel\Layer\Filter\AttributeFactory;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Eav\Model\Entity\Attribute\Option;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Url;
use Magento\Framework\View\Element\Template\Context;
use Magento\Swatches\Block\LayeredNavigation\RenderLayered;
use Magento\Swatches\Helper\Data;
use Magento\Swatches\Helper\Media;
use Magento\Theme\Block\Html\Pager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class RenderLayered Render Swatches at Layered Navigation
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RenderLayeredTest extends TestCase
{
    /**
     * @var RenderLayered|MockObject
     */
    private $block;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var Url|MockObject
     */
    private $urlBuilder;

    /**
     * @var Attribute|MockObject
     */
    private $eavAttributeMock;

    /**
     * @var AttributeFactory|MockObject
     */
    private $layerAttributeFactoryMock;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Layer\Filter\Attribute|MockObject
     */
    private $layerAttributeMock;

    /**
     * @var Data|MockObject
     */
    private $swatchHelperMock;

    /**
     * @var Media|MockObject
     */
    private $mediaHelperMock;

    /**
     * @var AbstractFilter|MockObject
     */
    private $filterMock;

    /**
     * @var Pager|MockObject
     */
    private $htmlBlockPagerMock;

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);
        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->urlBuilder = $this->createPartialMock(
            Url::class,
            ['getCurrentUrl', 'getRedirectUrl', 'getUrl']
        );
        $this->contextMock->method('getRequest')->willReturn($this->requestMock);
        $this->contextMock->method('getUrlBuilder')->willReturn($this->urlBuilder);
        $this->eavAttributeMock = $this->createMock(Attribute::class);
        $this->layerAttributeFactoryMock = $this->createPartialMock(
            AttributeFactory::class,
            ['create']
        );
        $this->layerAttributeMock = $this->createPartialMock(
            \Magento\Catalog\Model\ResourceModel\Layer\Filter\Attribute::class,
            ['getCount']
        );
        $this->swatchHelperMock = $this->createMock(Data::class);
        $this->mediaHelperMock = $this->createMock(Media::class);
        $this->filterMock = $this->createMock(AbstractFilter::class);
        $this->htmlBlockPagerMock = $this->createMock(Pager::class);

        $this->block = $this->getMockBuilder(RenderLayered::class)
            ->setMethods(['filter', 'eavAttribute'])
            ->setConstructorArgs(
                [
                    $this->contextMock,
                    $this->eavAttributeMock,
                    $this->layerAttributeFactoryMock,
                    $this->swatchHelperMock,
                    $this->mediaHelperMock,
                    [],
                    $this->htmlBlockPagerMock
                ]
            )
            ->getMock();
    }

    public function testSetSwatchFilter()
    {
        $this->block->method('filter')->willReturn($this->filterMock);
        $eavAttribute = $this->createMock(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class);
        $this->filterMock->expects($this->once())->method('getAttributeModel')->willReturn($eavAttribute);
        $this->block->method('eavAttribute')->willReturn($eavAttribute);
        $result = $this->block->setSwatchFilter($this->filterMock);
        $this->assertEquals($result, $this->block);
    }

    public function testGetSwatchData()
    {
        /** @var MockObject $item */
        $item1 = $this->createMock(Item::class);
        $item2 = $this->createMock(Item::class);
        $item3 = $this->createMock(Item::class);
        $item4 = $this->createMock(Item::class);

        $item1->method('__call')->withConsecutive(
            ['getValue'],
            ['getCount'],
            ['getValue'],
            ['getCount'],
            ['getLabel']
        )->willReturnOnConsecutiveCalls(
            'yellow',
            3,
            'yellow',
            3,
            'Yellow'
        );

        $item2->method('__call')->with('getValue')->willReturn('blue');

        $item3->method('__call')->withConsecutive(
            ['getValue'],
            ['getCount']
        )->willReturnOnConsecutiveCalls(
            'red',
            0
        );

        $item4->method('__call')->withConsecutive(
            ['getValue'],
            ['getCount'],
            ['getValue'],
            ['getCount'],
            ['getLabel']
        )->willReturnOnConsecutiveCalls(
            'green',
            3,
            'green',
            0,
            'Green'
        );

        $this->filterMock->method('getItems')->willReturnOnConsecutiveCalls(
            [$item1],
            [$item2],
            [$item3],
            [$item4]
        );

        $this->block->method('filter')->willReturn($this->filterMock);

        $option1 = $this->createMock(Option::class);
        $option1->method('getValue')->willReturn('yellow');

        $option2 = $this->createMock(Option::class);
        $option2->method('getValue')->willReturn(null);

        $option3 = $this->createMock(Option::class);
        $option3->method('getValue')->willReturn('red');

        $option4 = $this->createMock(Option::class);
        $option4->method('getValue')->willReturn('green');

        $eavAttribute = $this->createMock(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class);
        $eavAttribute->expects($this->once())
            ->method('getOptions')
            ->willReturn([$option1, $option2, $option3, $option4]);
        $eavAttribute->method('getIsFilterable')->willReturn(0);

        $this->filterMock->expects($this->once())->method('getAttributeModel')->willReturn($eavAttribute);
        $this->block->method('eavAttribute')->willReturn($eavAttribute);
        $this->block->setSwatchFilter($this->filterMock);

        $this->urlBuilder->expects($this->atLeastOnce())->method('getUrl')->willReturn('http://example.com/image.png');

        $optionsCount = [
            14 => 1,
            15 => 4,
        ];
        $this->layerAttributeMock
            ->method('getCount')
            ->willReturn($optionsCount);

        $this->block->getSwatchData();
    }

    public function testGetSwatchDataException()
    {
        $this->block->method('filter')->willReturn($this->filterMock);
        $this->block->setSwatchFilter($this->filterMock);
        $this->expectException(\RuntimeException::class);
        $this->block->getSwatchData();
    }

    public function testGetSwatchPath()
    {
        $this->mediaHelperMock
            ->expects($this->once())
            ->method('getSwatchAttributeImage')
            ->with('swatch_image', '/m/a/magento.jpg')
            ->willReturn('http://domain.com/path_to_swatch_image/m/a/magento.jpg');
        $result = $this->block->getSwatchPath('swatch_image', '/m/a/magento.jpg');
        $this->assertEquals($result, 'http://domain.com/path_to_swatch_image/m/a/magento.jpg');
    }
}
