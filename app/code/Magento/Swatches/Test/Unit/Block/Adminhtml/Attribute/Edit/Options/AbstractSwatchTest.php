<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Test\Unit\Block\Adminhtml\Attribute\Edit\Options;

/**
 * Backend swatch abstract block
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $connectionMock;

    protected function setUp()
    {
        $this->contextMock = $this->getMock(\Magento\Backend\Block\Template\Context::class, [], [], '', false);
        $this->registryMock = $this->getMock(\Magento\Framework\Registry::class, [], [], '', false);
        $this->attrOptionCollectionFactoryMock = $this->getMock(
            \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->mediaConfigMock = $this->getMock(\Magento\Catalog\Model\Product\Media\Config::class, [], [], '', false);
        $this->universalFactoryMock = $this->getMock(
            \Magento\Framework\Validator\UniversalFactory::class,
            [],
            [],
            '',
            false
        );
        $this->swatchHelperMock = $this->getMock(\Magento\Swatches\Helper\Media::class, [], [], '', false);

        $this->block = $this->getMock(
            \Magento\Swatches\Block\Adminhtml\Attribute\Edit\Options\AbstractSwatch::class,
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

        $this->connectionMock = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['quoteInto'])
            ->getMockForAbstractClass();
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
                \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option::class,
                ['getId', 'getValue', 'getLabel'],
                [],
                '',
                false
            );

            $attrOptionCollectionMock = $objectManager->getCollectionMock(
                \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection::class,
                [$option, $option]
            );

            $this->attrOptionCollectionFactoryMock
                ->expects($this->once())
                ->method('create')
                ->willReturn($attrOptionCollectionMock);

            $attribute = $this->getMock(
                \Magento\Eav\Model\ResourceModel\Entity\Attribute::class,
                ['getId'],
                [],
                '',
                false
            );
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

            $this->connectionMock
                ->expects($this->any())
                ->method('quoteInto')
                ->willReturn('quoted_string_with_value');

            $attrOptionCollectionMock
                ->expects($this->any())
                ->method('getConnection')
                ->willReturn($this->connectionMock);

            $zendDbSelectMock = $this->getMock(\Magento\Framework\DB\Select::class, [], [], '', false);
            $attrOptionCollectionMock->expects($this->any())->method('getSelect')->willReturn($zendDbSelectMock);
            $zendDbSelectMock->expects($this->any())->method('joinLeft')->willReturnSelf();

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
