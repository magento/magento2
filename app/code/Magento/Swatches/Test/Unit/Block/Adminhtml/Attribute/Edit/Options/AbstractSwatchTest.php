<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Validator\UniversalFactory;
use Magento\Swatches\Block\Adminhtml\Attribute\Edit\Options\AbstractSwatch;
use Magento\Swatches\Helper\Media;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\View\Element\Html\Select as HtmlSelect;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Backend swatch abstract block
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AbstractSwatchTest extends TestCase
{
    use MockCreationTrait;
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

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        // Initialize ObjectManager for global access
        $objectManager = new ObjectManager($this);
        $objects = [
            [
                Data::class,
                $this->createMock(Data::class)
            ],
            [
                HtmlSelect::class,
                $this->createMock(HtmlSelect::class)
            ]
        ];
        $objectManager->prepareObjectManager($objects);

        $this->contextMock = $this->createMock(Context::class);
        $this->registryMock = $this->createMock(Registry::class);
        $this->attrOptionCollectionFactoryMock = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );
        $this->mediaConfigMock = $this->createMock(Config::class);
        $this->universalFactoryMock = $this->createMock(UniversalFactory::class);
        $this->swatchHelperMock = $this->createMock(Media::class);

        $this->block = $this->createPartialMock(AbstractSwatch::class, ['getData']);
        // Set constructor arguments using reflection
        $reflection = new \ReflectionClass($this->block);
        $constructor = $reflection->getConstructor();
        if ($constructor) {
            $constructor->invokeArgs($this->block, [
                $this->contextMock,
                $this->registryMock,
                $this->attrOptionCollectionFactoryMock,
                $this->universalFactoryMock,
                $this->mediaConfigMock,
                $this->swatchHelperMock,
                []
            ]);
        }
        $this->connectionMock = $this->createMock(AdapterInterface::class);
    }

    /**
     * @return void
     */
    #[DataProvider('dataForGetStoreOptionValues')]
    public function testGetStoreOptionValues($values): void
    {
        $this->block->expects($this->once())->method('getData')->with('store_option_values_1')->willReturn($values);
        if ($values === null) {
            $objectManager = new ObjectManager($this);

            $option = $this->createPartialMockWithReflection(
                Option::class,
                ['getId', 'getValue', 'getLabel']
            );

            $attrOptionCollectionMock = $objectManager->getCollectionMock(
                Collection::class,
                [$option, $option]
            );

            $this->attrOptionCollectionFactoryMock
                ->expects($this->once())
                ->method('create')
                ->willReturn($attrOptionCollectionMock);

            $attribute = $this->createPartialMockWithReflection(
                Attribute::class,
                ['getId']
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

            $option
                ->method('getId')
                ->willReturnOnConsecutiveCalls(14, 14, 15, 15);
            $option
                ->method('getLabel')
                ->willReturnOnConsecutiveCalls('#0000FF', '#000000');
            $option
                ->method('getValue')
                ->willReturnOnConsecutiveCalls('Blue', 'Black');

            $values = [
                14 => 'Blue',
                'swatch' => [
                    14 => '#0000FF',
                    15 => '#000000'
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
    public static function dataForGetStoreOptionValues(): array
    {
        return [
            [
                [
                    14 => 'Blue',
                    15 => 'Black'
                ]
            ],
            [
                null
            ]
        ];
    }
}
