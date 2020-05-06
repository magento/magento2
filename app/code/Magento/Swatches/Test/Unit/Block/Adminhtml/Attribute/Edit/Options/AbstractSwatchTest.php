<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Swatches\Test\Unit\Block\Adminhtml\Attribute\Edit\Options;

use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Validator\UniversalFactory;
use Magento\Swatches\Block\Adminhtml\Attribute\Edit\Options\AbstractSwatch;
use Magento\Swatches\Helper\Media;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Backend swatch abstract block
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AbstractSwatchTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $contextMock;

    /**
     * @var MockObject
     */
    protected $registryMock;

    /**
     * @var MockObject
     */
    protected $attrOptionCollectionFactoryMock;

    /**
     * @var MockObject
     */
    protected $mediaConfigMock;

    /**
     * @var MockObject
     */
    protected $universalFactoryMock;

    /**
     * @var MockObject
     */
    protected $swatchHelperMock;

    /**
     * @var MockObject
     */
    protected $block;

    /**
     * @var AdapterInterface|MockObject
     */
    protected $connectionMock;

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);
        $this->registryMock = $this->createMock(Registry::class);
        $this->attrOptionCollectionFactoryMock = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );
        $this->mediaConfigMock = $this->createMock(Config::class);
        $this->universalFactoryMock = $this->createMock(UniversalFactory::class);
        $this->swatchHelperMock = $this->createMock(Media::class);

        $this->block = $this->getMockBuilder(
            AbstractSwatch::class
        )
            ->setMethods(['getData'])
            ->setConstructorArgs(
                [
                    'context' => $this->contextMock,
                    'registry' => $this->registryMock,
                    'attrOptionCollectionFactory' => $this->attrOptionCollectionFactoryMock,
                    'universalFactory' => $this->universalFactoryMock,
                    'mediaConfig' => $this->mediaConfigMock,
                    'swatchHelper' => $this->swatchHelperMock,
                    'data' => []
                ]
            )
            ->getMock();
        $this->connectionMock = $this->getMockBuilder(AdapterInterface::class)
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
            $objectManager = new ObjectManager($this);

            $option = $this->getMockBuilder(Option::class)
                ->addMethods(['getId', 'getValue', 'getLabel'])
                ->disableOriginalConstructor()
                ->getMock();

            $attrOptionCollectionMock = $objectManager->getCollectionMock(
                Collection::class,
                [$option, $option]
            );

            $this->attrOptionCollectionFactoryMock
                ->expects($this->once())
                ->method('create')
                ->willReturn($attrOptionCollectionMock);

            $attribute = $this->getMockBuilder(Attribute::class)
                ->addMethods(['getId'])
                ->disableOriginalConstructor()
                ->getMock();
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
                ->willReturnSelf();

            $this->connectionMock
                ->expects($this->any())
                ->method('quoteInto')
                ->willReturn('quoted_string_with_value');

            $attrOptionCollectionMock
                ->expects($this->any())
                ->method('getConnection')
                ->willReturn($this->connectionMock);

            $zendDbSelectMock = $this->createMock(Select::class);
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

    /**
     * @return array
     */
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
