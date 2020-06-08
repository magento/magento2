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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class RenderLayered Render Swatches at Layered Navigation
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RenderLayeredTest extends TestCase
{
    /** @var MockObject */
    protected $contextMock;

    /** @var MockObject */
    protected $requestMock;

    /** @var MockObject */
    protected $urlBuilder;

    /** @var MockObject */
    protected $eavAttributeMock;

    /** @var MockObject */
    protected $layerAttributeFactoryMock;

    /** @var MockObject */
    protected $layerAttributeMock;

    /** @var MockObject */
    protected $swatchHelperMock;

    /** @var MockObject */
    protected $mediaHelperMock;

    /** @var MockObject */
    protected $filterMock;

    /** @var MockObject */
    protected $block;

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);
        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->urlBuilder = $this->createPartialMock(
            Url::class,
            ['getCurrentUrl', 'getRedirectUrl', 'getUrl']
        );
        $this->contextMock->expects($this->any())->method('getRequest')->willReturn($this->requestMock);
        $this->contextMock->expects($this->any())->method('getUrlBuilder')->willReturn($this->urlBuilder);
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

        $item1->expects($this->any())->method('__call')->withConsecutive(
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

        $item2->expects($this->any())->method('__call')->with('getValue')->willReturn('blue');

        $item3->expects($this->any())->method('__call')->withConsecutive(
            ['getValue'],
            ['getCount']
        )->willReturnOnConsecutiveCalls(
            'red',
            0
        );

        $item4->expects($this->any())->method('__call')->withConsecutive(
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
        $option1->expects($this->any())->method('getValue')->willReturn('yellow');

        $option2 = $this->createMock(Option::class);
        $option2->expects($this->any())->method('getValue')->willReturn(null);

        $option3 = $this->createMock(Option::class);
        $option3->expects($this->any())->method('getValue')->willReturn('red');

        $option4 = $this->createMock(Option::class);
        $option4->expects($this->any())->method('getValue')->willReturn('green');

        $eavAttribute = $this->createMock(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class);
        $eavAttribute->expects($this->once())
            ->method('getOptions')
            ->willReturn([$option1, $option2, $option3, $option4]);
        $eavAttribute->expects($this->any())->method('getIsFilterable')->willReturn(0);

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
        $this->expectException('\RuntimeException');
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
