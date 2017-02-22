<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Test\Unit\Block\LayeredNavigation;

/**
 * Class RenderLayered Render Swatches at Layered Navigation
 */
class RenderLayeredTest extends \PHPUnit_Framework_TestCase
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

    public function setUp()
    {
        $this->contextMock = $this->getMock('\Magento\Framework\View\Element\Template\Context', [], [], '', false);
        $this->requestMock = $this->getMock('\Magento\Framework\App\Request', ['getParams'], [], '', false);
        $this->urlBuilder = $this->getMock(
            '\Magento\Framework\Url',
            ['getCurrentUrl', 'getRedirectUrl', 'getUrl'],
            [],
            '',
            false
        );
        $this->contextMock->expects($this->any())->method('getRequest')->willReturn($this->requestMock);
        $this->contextMock->expects($this->any())->method('getUrlBuilder')->willReturn($this->urlBuilder);
        $this->eavAttributeMock = $this->getMock('\Magento\Eav\Model\Entity\Attribute', [], [], '', false);
        $this->layerAttributeFactoryMock = $this->getMock(
            '\Magento\Catalog\Model\ResourceModel\Layer\Filter\AttributeFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->layerAttributeMock = $this->getMock(
            '\Magento\Catalog\Model\ResourceModel\Layer\Filter\Attribute',
            ['getCount'],
            [],
            '',
            false
        );
        $this->swatchHelperMock = $this->getMock('\Magento\Swatches\Helper\Data', [], [], '', false);
        $this->mediaHelperMock = $this->getMock('\Magento\Swatches\Helper\Media', [], [], '', false);
        $this->filterMock = $this->getMock('Magento\Catalog\Model\Layer\Filter\AbstractFilter', [], [], '', false);

        $this->block = $this->getMock(
            '\Magento\Swatches\Block\LayeredNavigation\RenderLayered',
            ['filter', 'eavAttribute'],
            [
                $this->contextMock,
                $this->eavAttributeMock,
                $this->layerAttributeFactoryMock,
                $this->swatchHelperMock,
                $this->mediaHelperMock,
                [],
            ],
            '',
            true
        );
    }

    public function testSetSwatchFilter()
    {
        $this->block->method('filter')->willReturn($this->filterMock);
        $eavAttribute = $this->getMock('\Magento\Catalog\Model\ResourceModel\Eav\Attribute', [], [], '', false);
        $this->filterMock->expects($this->once())->method('getAttributeModel')->willReturn($eavAttribute);
        $this->block->method('eavAttribute')->willReturn($eavAttribute);
        $result = $this->block->setSwatchFilter($this->filterMock);
        $this->assertEquals($result, $this->block);
    }

    public function testGetSwatchData()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject $item */
        $item1 = $this->getMock('Magento\Catalog\Model\Layer\Filter\Item', [], [], '', false);
        $item2 = $this->getMock('Magento\Catalog\Model\Layer\Filter\Item', [], [], '', false);
        $item3 = $this->getMock('Magento\Catalog\Model\Layer\Filter\Item', [], [], '', false);
        $item4 = $this->getMock('Magento\Catalog\Model\Layer\Filter\Item', [], [], '', false);

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

        $option1 = $this->getMock('\Magento\Eav\Model\Entity\Attribute\Option', [], [], '', false);
        $option1->expects($this->any())->method('getValue')->willReturn('yellow');

        $option2 = $this->getMock('\Magento\Eav\Model\Entity\Attribute\Option', [], [], '', false);
        $option2->expects($this->any())->method('getValue')->willReturn(null);

        $option3 = $this->getMock('\Magento\Eav\Model\Entity\Attribute\Option', [], [], '', false);
        $option3->expects($this->any())->method('getValue')->willReturn('red');

        $option4 = $this->getMock('\Magento\Eav\Model\Entity\Attribute\Option', [], [], '', false);
        $option4->expects($this->any())->method('getValue')->willReturn('green');

        $eavAttribute = $this->getMock('\Magento\Catalog\Model\ResourceModel\Eav\Attribute', [], [], '', false);
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
        $this->setExpectedException('\RuntimeException');
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
