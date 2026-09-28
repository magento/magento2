<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Layer\Filter;

use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Filter\Attribute as FilterAttribute;
use Magento\Catalog\Model\Layer\Filter\Item;
use Magento\Catalog\Model\Layer\Filter\Item\DataBuilder;
use Magento\Catalog\Model\Layer\Filter\ItemFactory;
use Magento\Catalog\Model\Layer\State;
use Magento\Catalog\Model\ResourceModel\Layer\Filter\Attribute;
use Magento\Catalog\Model\ResourceModel\Layer\Filter\AttributeFactory;
use Magento\Eav\Model\Entity\Attribute as EntityAttribute;
use Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Filter\StripTags;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AttributeTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var Attribute|MockObject
     */
    private $filterAttribute;

    /**
     * @var FilterAttribute
     */
    private $target;

    /**
     * @var AbstractFrontend|MockObject
     */
    private $frontend;

    /**
     * @var State|MockObject
     */
    private $state;

    /**
     * @var EntityAttribute|MockObject
     */
    private $attribute;

    /**
     * @var RequestInterface|MockObject
     */
    private $request;

    /**
     * @var AttributeFactory|MockObject
     */
    private $filterAttributeFactory;

    /**
     * @var ItemFactory|MockObject
     */
    private $filterItemFactory;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    /**
     * @var Layer|MockObject
     */
    private $layer;

    /**
     * @var DataBuilder|MockObject
     */
    private $itemDataBuilder;

    /**
     * @inheritDoc
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        /** @var ItemFactory $filterItemFactory */
        $this->filterItemFactory = $this->createPartialMock(ItemFactory::class, ['create']);

        /** @var StoreManagerInterface $storeManager */
        $this->storeManager = $this->createStub(StoreManagerInterface::class);
        /** @var Layer $layer */
        $this->layer = $this->createPartialMock(Layer::class, ['getState']);
        /** @var DataBuilder $itemDataBuilder */
        $this->itemDataBuilder = $this->createPartialMock(DataBuilder::class, ['addItemData', 'build']);

        $this->filterAttribute = $this->createPartialMock(Attribute::class, ['getCount', 'applyFilterToCollection']);

        $this->filterAttribute->expects($this->any())
            ->method('applyFilterToCollection')->willReturnSelf();

        $this->filterAttributeFactory = $this->createPartialMock(AttributeFactory::class, ['create']);

        $this->filterAttributeFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->filterAttribute);

        $this->state = $this->createPartialMock(State::class, ['addFilter']);
        $this->layer->method('getState')->willReturn($this->state);

        $this->frontend = $this->createPartialMock(AbstractFrontend::class, ['getOption', 'getSelectOptions']);
        $this->attribute = $this->createPartialMock(
            EntityAttribute::class,
            ['getAttributeCode', 'getFrontend']
        );
        $this->attribute->expects($this->atLeastOnce())
            ->method('getFrontend')
            ->willReturn($this->frontend);

        $this->request = $this->createMock(RequestInterface::class);

        $stripTagsFilter = $this->createPartialMock(StripTags::class, ['filter']);
        $stripTagsFilter->expects($this->any())
            ->method('filter')
            ->willReturnArgument(0);

        $string = $this->createPartialMock(StringUtils::class, ['strlen']);
        $string->expects($this->any())
            ->method('strlen')
            ->willReturnCallback(
                function ($value) {
                    return strlen($value);
                }
            );

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->target = $objectManagerHelper->getObject(
            FilterAttribute::class,
            [
                'filterItemFactory' => $this->filterItemFactory,
                'storeManager' => $this->storeManager,
                'layer' => $this->layer,
                'itemDataBuilder' => $this->itemDataBuilder,
                'filterAttributeFactory' => $this->filterAttributeFactory,
                'tagFilter' => $stripTagsFilter,
                'string' => $string
            ]
        );
    }

    /**
     * @return void
     */
    public function testApplyFilter(): void
    {
        $attributeCode = 'attributeCode';
        $attributeValue = 'attributeValue';
        $attributeLabel = 'attributeLabel';

        $this->attribute->method('getAttributeCode')->willReturn($attributeCode);

        $this->target->setAttributeModel($this->attribute);

        $this->request->expects($this->once())
            ->method('getParam')
            ->with($attributeCode)
            ->willReturn($attributeValue);

        $this->frontend->expects($this->once())
            ->method('getOption')
            ->with($attributeValue)
            ->willReturn($attributeLabel);

        $filterItem = $this->createFilterItem($attributeLabel, $attributeValue, 0);

        $this->state->expects($this->once())
            ->method('addFilter')
            ->with($filterItem)->willReturnSelf();

        $result = $this->target->apply($this->request);

        $this->assertEquals($this->target, $result);
    }

    /**
     * @return void
     */
    public function testGetItems(): void
    {
        $attributeCode = 'attributeCode';
        $attributeValue = 'attributeValue';
        $attributeLabel = 'attributeLabel';

        $this->attribute->expects($this->once())
            ->method('getAttributeCode')
            ->willReturn($attributeCode);

        $this->target->setAttributeModel($this->attribute);

        $this->request->expects($this->once())
            ->method('getParam')
            ->with($attributeCode)
            ->willReturn($attributeValue);

        $this->frontend->expects($this->once())
            ->method('getOption')
            ->with($attributeValue)
            ->willReturn($attributeLabel);

        $filterItem = $this->createFilterItem($attributeLabel, $attributeValue, 0);

        $this->state->expects($this->once())
            ->method('addFilter')
            ->with($filterItem)->willReturnSelf();

        $expectedFilterItems = [];

        $result = $this->target->apply($this->request)->getItems();

        $this->assertEquals($expectedFilterItems, $result);
    }

    /**
     * @param string $label
     * @param string $value
     * @param int $count
     *
     * @return Item|MockObject
     */
    private function createFilterItem(string $label, string $value, int $count): Item
    {
        $filterItem = $this->createPartialMockWithReflection(
            Item::class,
            ['setFilter', 'setLabel', 'setValue', 'setCount']
        );

        $filterItem->expects($this->once())
            ->method('setFilter')
            ->with($this->target)->willReturnSelf();

        $filterItem->expects($this->once())
            ->method('setLabel')
            ->with($label)->willReturnSelf();

        $filterItem->expects($this->once())
            ->method('setValue')
            ->with($value)->willReturnSelf();

        $filterItem->expects($this->once())
            ->method('setCount')
            ->with($count)->willReturnSelf();

        $this->filterItemFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($filterItem);

        return $filterItem;
    }
}
