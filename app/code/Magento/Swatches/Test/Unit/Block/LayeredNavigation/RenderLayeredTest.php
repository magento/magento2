<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Test\Unit\Block\LayeredNavigation;

/**
 * Class RenderLayered Render Swatches at Layered Navigation
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RenderLayeredTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $contextMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $requestMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $urlBuilder;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $eavAttributeMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $layerAttributeFactoryMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $layerAttributeMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $swatchHelperMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $mediaHelperMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $filterMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $block;

    protected function setUp()
    {
        $this->contextMock = $this->createMock(\Magento\Framework\View\Element\Template\Context::class);
        $this->requestMock = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->urlBuilder = $this->createPartialMock(
            \Magento\Framework\Url::class,
            ['getCurrentUrl', 'getRedirectUrl', 'getUrl']
        );
        $this->contextMock->expects($this->any())->method('getRequest')->willReturn($this->requestMock);
        $this->contextMock->expects($this->any())->method('getUrlBuilder')->willReturn($this->urlBuilder);
        $this->eavAttributeMock = $this->createMock(\Magento\Eav\Model\Entity\Attribute::class);
        $this->layerAttributeFactoryMock = $this->createPartialMock(
            \Magento\Catalog\Model\ResourceModel\Layer\Filter\AttributeFactory::class,
            ['create']
        );
        $this->layerAttributeMock = $this->createPartialMock(
            \Magento\Catalog\Model\ResourceModel\Layer\Filter\Attribute::class,
            ['getCount']
        );
        $this->swatchHelperMock = $this->createMock(\Magento\Swatches\Helper\Data::class);
        $this->mediaHelperMock = $this->createMock(\Magento\Swatches\Helper\Media::class);
        $this->filterMock = $this->createMock(\Magento\Catalog\Model\Layer\Filter\AbstractFilter::class);

        $this->block = $this->getMockBuilder(\Magento\Swatches\Block\LayeredNavigation\RenderLayered::class)
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
        /** @var \PHPUnit_Framework_MockObject_MockObject $item */
        $item1 = $this->createMock(\Magento\Catalog\Model\Layer\Filter\Item::class);
        $item2 = $this->createMock(\Magento\Catalog\Model\Layer\Filter\Item::class);
        $item3 = $this->createMock(\Magento\Catalog\Model\Layer\Filter\Item::class);
        $item4 = $this->createMock(\Magento\Catalog\Model\Layer\Filter\Item::class);

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

        $option1 = $this->createMock(\Magento\Eav\Model\Entity\Attribute\Option::class);
        $option1->expects($this->any())->method('getValue')->willReturn('yellow');

        $option2 = $this->createMock(\Magento\Eav\Model\Entity\Attribute\Option::class);
        $option2->expects($this->any())->method('getValue')->willReturn(null);

        $option3 = $this->createMock(\Magento\Eav\Model\Entity\Attribute\Option::class);
        $option3->expects($this->any())->method('getValue')->willReturn('red');

        $option4 = $this->createMock(\Magento\Eav\Model\Entity\Attribute\Option::class);
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
