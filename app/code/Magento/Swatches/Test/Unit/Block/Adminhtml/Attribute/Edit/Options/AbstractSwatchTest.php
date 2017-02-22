<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Test\Unit\Block\Adminhtml\Attribute\Edit\Options;

/**
 * Backend swatch abstract block
 */
class AbstractSwatchTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $registryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $attrOptionCollectionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $mediaConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $universalFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $swatchHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $block;

    public function setUp()
    {
        $this->contextMock = $this->getMock('\Magento\Backend\Block\Template\Context', [], [], '', false);
        $this->registryMock = $this->getMock('\Magento\Framework\Registry', [], [], '', false);
        $this->attrOptionCollectionFactoryMock = $this->getMock(
            '\Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->mediaConfigMock = $this->getMock('\Magento\Catalog\Model\Product\Media\Config', [], [], '', false);
        $this->universalFactoryMock = $this->getMock(
            '\Magento\Framework\Validator\UniversalFactory',
            [],
            [],
            '',
            false
        );
        $this->swatchHelperMock = $this->getMock('\Magento\Swatches\Helper\Media', [], [], '', false);

        $this->block = $this->getMock(
            'Magento\Swatches\Block\Adminhtml\Attribute\Edit\Options\AbstractSwatch',
            ['getData'],
            [
                'context' => $this->contextMock,
                'registry' => $this->registryMock,
                'attrOptionCollectionFactory' => $this->attrOptionCollectionFactoryMock,
                'universalFactory' => $this->universalFactoryMock,
                'mediaConfig' => $this->mediaConfigMock,
                'swatchHelper' => $this->swatchHelperMock,
                'data' => []
            ],
            '',
            true
        );


    }

    /**
     * @dataProvider dataForGetStoreOptionValues
     */
    public function testGetStoreOptionValues($values)
    {
        $this->block->expects($this->once())->method('getData')->with('store_option_values_1')->willReturn($values);
        if ($values === null) {
            $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

            $option = $this->getMock(
                '\Magento\Eav\Model\ResourceModel\Entity\Attribute\Option',
                ['getId', 'getValue', 'getLabel'],
                [],
                '',
                false
            );

            $attrOptionCollectionMock = $objectManager->getCollectionMock(
                '\Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection',
                [$option, $option]
            );

            $this->attrOptionCollectionFactoryMock
                ->expects($this->once())
                ->method('create')
                ->willReturn($attrOptionCollectionMock);

            $attribute = $this->getMock('\Magento\Eav\Model\ResourceModel\Entity\Attribute', ['getId'], [], '', false);
            $attribute->expects($this->once())->method('getId')->willReturn(23);

            $this->registryMock
                ->expects($this->once())
                ->method('registry')
                ->with('entity_attribute')
                ->willReturn($attribute);

            $attrOptionCollectionMock
                ->expects($this->once())
                ->method('setAttributeFilter')
                ->with(23)
                ->will($this->returnSelf());

            $attrOptionCollectionMock
                ->expects($this->once())
                ->method('setStoreFilter')
                ->with(1, false)
                ->will($this->returnSelf());

            $zendDbSelectMock = $this->getMock('Magento\Framework\DB\Select', [], [], '', false);
            $attrOptionCollectionMock->expects($this->once())->method('getSelect')->willReturn($zendDbSelectMock);
            $zendDbSelectMock->expects($this->once())->method('joinLeft')->will($this->returnSelf());

            $option->expects($this->at(0))->method('getId')->willReturn(14);
            $option->expects($this->at(1))->method('getValue')->willReturn('Blue');
            $option->expects($this->at(2))->method('getId')->willReturn(14);
            $option->expects($this->at(3))->method('getLabel')->willReturn('#0000FF');
            $option->expects($this->at(4))->method('getId')->willReturn(15);
            $option->expects($this->at(5))->method('getValue')->willReturn('Black');
            $option->expects($this->at(6))->method('getId')->willReturn(15);
            $option->expects($this->at(7))->method('getLabel')->willReturn('#000000');

            $values = [
                14 => 'Blue',
                'swatch' => [
                    14 => '#0000FF',
                    15 => '#000000',
                ],
                15 =>'Black'
            ];
        }
        $result = $this->block->getStoreOptionValues(1);
        $this->assertEquals($result, $values);
    }

    public function dataForGetStoreOptionValues()
    {
        return [
            [
                [
                    14 => 'Blue',
                    15 => 'Black',
                ],
            ],
            [
                null,
            ],
        ];
    }
}
