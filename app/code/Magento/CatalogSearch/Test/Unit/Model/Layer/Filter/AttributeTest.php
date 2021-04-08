<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Test\Unit\Model\Layer\Filter;

use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Catalog\Model\Layer\Filter\Item;
use Magento\Catalog\Model\Layer\Filter\Item\DataBuilder;
use Magento\Catalog\Model\Layer\Filter\ItemFactory;
use Magento\Catalog\Model\Layer\State;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as EavAttribute;
use Magento\Catalog\Model\ResourceModel\Layer\Filter\AttributeFactory;
use Magento\CatalogSearch\Model\Layer\Filter\Attribute;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection;
use Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Filter\StripTags;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for \Magento\CatalogSearch\Model\Layer\Filter\Attribute class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AttributeTest extends TestCase
{
    /**
     * @var Attribute
     */
    private $target;

    /** @var AbstractFrontend|MockObject */
    private $frontend;

    /** @var Collection|MockObject */
    private $fulltextCollection;

    /** @var State|MockObject */
    private $state;

    /** @var EavAttribute|MockObject */
    private $attribute;

    /** @var RequestInterface|MockObject */
    private $request;

    /** @var AttributeFactory|MockObject */
    private $filterAttributeFactory;

    /** @var ItemFactory|MockObject */
    private $filterItemFactory;

    /** @var StoreManagerInterface|MockObject */
    private $storeManager;

    /** @var Layer|MockObject */
    private $layer;

    /** @var  DataBuilder|MockObject */
    private $itemDataBuilder;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        /** @var ItemFactory $filterItemFactory */
        $this->filterItemFactory = $this->getMockBuilder(ItemFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        /** @var StoreManagerInterface $storeManager */
        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();
        /** @var Layer $layer */
        $this->layer = $this->getMockBuilder(Layer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getState', 'getProductCollection'])
            ->getMock();
        $this->fulltextCollection =
            $this->getMockBuilder(Collection::class)
                ->disableOriginalConstructor()
                ->setMethods(['addFieldToFilter', 'getFacetedData', 'getSize'])
                ->getMock();
        $this->layer->expects($this->atLeastOnce())
            ->method('getProductCollection')
            ->willReturn($this->fulltextCollection);
        /** @var DataBuilder $itemDataBuilder */
        $this->itemDataBuilder = $this->getMockBuilder(DataBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['addItemData', 'build'])
            ->getMock();

        $this->filterAttributeFactory = $this->getMockBuilder(AttributeFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->state = $this->getMockBuilder(State::class)
            ->disableOriginalConstructor()
            ->setMethods(['addFilter'])
            ->getMock();
        $this->layer->expects($this->any())
            ->method('getState')
            ->willReturn($this->state);

        $this->frontend = $this->getMockBuilder(AbstractFrontend::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOption', 'getSelectOptions'])
            ->getMock();
        $this->attribute = $this->getMockBuilder(EavAttribute::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'getAttributeCode',
                'getFrontend',
                'getIsFilterable',
                'getBackendType',
            ])
            ->getMock();

        $this->request = $this->getMockBuilder(RequestInterface::class)
            ->setMethods(['getParam'])
            ->getMockForAbstractClass();

        $stripTagsFilter = $this->getMockBuilder(StripTags::class)
            ->disableOriginalConstructor()
            ->setMethods(['filter'])
            ->getMock();
        $stripTagsFilter->expects($this->any())
            ->method('filter')
            ->willReturnArgument(0);

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->target = $objectManagerHelper->getObject(
            Attribute::class,
            [
                'filterItemFactory' => $this->filterItemFactory,
                'storeManager' => $this->storeManager,
                'layer' => $this->layer,
                'itemDataBuilder' => $this->itemDataBuilder,
                'filterAttributeFactory' => $this->filterAttributeFactory,
                'tagFilter' => $stripTagsFilter,
            ]
        );
    }

    /**
     * @dataProvider attributeDataProvider
     * @param array $attributeData
     * @return void
     */
    public function testApplyFilter(array $attributeData)
    {
        $this->attribute->expects($this->exactly(2))
            ->method('getAttributeCode')
            ->willReturn($attributeData['attribute_code']);
        $this->attribute->expects($this->atLeastOnce())
            ->method('getFrontend')
            ->willReturn($this->frontend);

        $this->target->setAttributeModel($this->attribute);

        $this->request->expects($this->once())
            ->method('getParam')
            ->with($attributeData['attribute_code'])
            ->willReturn($attributeData['attribute_value']);

        $this->attribute->expects($this->once())
            ->method('getBackendType')
            ->willReturn($attributeData['backend_type']);

        $this->fulltextCollection->expects($this->once())
            ->method('addFieldToFilter')
            ->with(
                $attributeData['attribute_code'],
                $attributeData['attribute_value']
            )
            ->willReturnSelf();

        $this->frontend->expects($this->once())
            ->method('getOption')
            ->with($attributeData['attribute_value'])
            ->willReturn($attributeData['attribute_label']);

        $filterItem = $this->createFilterItem(
            0,
            $attributeData['attribute_label'],
            $attributeData['attribute_value'],
            0
        );

        $filterItem->expects($this->once())
            ->method('setFilter')
            ->with($this->target)
            ->willReturnSelf();

        $filterItem->expects($this->once())
            ->method('setLabel')
            ->with($attributeData['attribute_label'])
            ->willReturnSelf();

        $filterItem->expects($this->once())
            ->method('setValue')
            ->with($attributeData['attribute_value'])
            ->willReturnSelf();

        $filterItem->expects($this->once())
            ->method('setCount')
            ->with(0)
            ->willReturnSelf();

        $this->state->expects($this->once())
            ->method('addFilter')
            ->with($filterItem)
            ->willReturnSelf();

        $result = $this->target->apply($this->request);

        $this->assertEquals($this->target, $result);
    }

    /**
     * @return array
     */
    public function attributeDataProvider(): array
    {
        return [
            'Attribute with \'text\' backend type' => [
                [
                    'attribute_code' => 'attributeCode',
                    'attribute_value' => 'attributeValue',
                    'attribute_label' => 'attributeLabel',
                    'backend_type' => 'text',
                ],
            ],
            'Attribute with \'int\' backend type' => [
                [
                    'attribute_code' => 'attributeCode',
                    'attribute_value' => '0',
                    'attribute_label' => 'attributeLabel',
                    'backend_type' => 'int',
                ],
            ],
        ];
    }

    public function testGetItemsWithApply()
    {
        $attributeCode = 'attributeCode';
        $attributeValue = 'attributeValue';
        $attributeLabel = 'attributeLabel';

        $this->attribute->expects($this->exactly(2))
            ->method('getAttributeCode')
            ->willReturn($attributeCode);
        $this->attribute->expects($this->atLeastOnce())
            ->method('getFrontend')
            ->willReturn($this->frontend);

        $this->target->setAttributeModel($this->attribute);

        $this->request->expects($this->once())
            ->method('getParam')
            ->with($attributeCode)
            ->willReturn($attributeValue);

        $this->fulltextCollection->expects($this->once())
            ->method('addFieldToFilter')
            ->with($attributeCode, $attributeValue)->willReturnSelf();

        $this->frontend->expects($this->once())
            ->method('getOption')
            ->with($attributeValue)
            ->willReturn($attributeLabel);
        $filterItem = $this->createFilterItem(0, $attributeLabel, $attributeValue, 0);

        $this->state->expects($this->once())
            ->method('addFilter')
            ->with($filterItem)->willReturnSelf();

        $expectedFilterItems = [];

        $result = $this->target->apply($this->request)->getItems();

        $this->assertEquals($expectedFilterItems, $result);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetItemsWithoutApply()
    {
        $attributeCode = 'attributeCode';
        $selectedOptions = [
            [
                'label' => 'selectedOptionLabel1',
                'value' => 'selectedOptionValue1',
                'count' => 25,
            ],
            [
                'label' => 'selectedOptionLabel2',
                'value' => 'selectedOptionValue2',
                'count' => 13,
            ],
            [
                'label' => 'selectedOptionLabel3',
                'value' => 'selectedOptionValue3',
                'count' => 10,
            ],
        ];
        $facetedData = [
            'selectedOptionValue1' => ['count' => 10],
            'selectedOptionValue2' => ['count' => 45],
            'selectedOptionValue3' => ['count' => 50],
        ];

        $builtData = [
            [
                'label' => $selectedOptions[0]['label'],
                'value' => $selectedOptions[0]['value'],
                'count' => $facetedData[$selectedOptions[0]['value']]['count'],
            ],
            [
                'label' => $selectedOptions[1]['label'],
                'value' => $selectedOptions[1]['value'],
                'count' => $facetedData[$selectedOptions[1]['value']]['count'],
            ],
            [
                'label' => $selectedOptions[2]['label'],
                'value' => $selectedOptions[2]['value'],
                'count' => $facetedData[$selectedOptions[2]['value']]['count'],
            ],
        ];

        $this->attribute->expects($this->exactly(2))
            ->method('getAttributeCode')
            ->willReturn($attributeCode);
        $this->attribute->expects($this->atLeastOnce())
            ->method('getFrontend')
            ->willReturn($this->frontend);

        $this->target->setAttributeModel($this->attribute);

        $this->frontend->expects($this->once())
            ->method('getSelectOptions')
            ->willReturn($selectedOptions);

        $this->fulltextCollection->expects($this->once())
            ->method('getFacetedData')
            ->willReturn($facetedData);

        $this->itemDataBuilder->expects($this->at(0))
            ->method('addItemData')
            ->with(
                $selectedOptions[0]['label'],
                $selectedOptions[0]['value'],
                $facetedData[$selectedOptions[0]['value']]['count']
            )->willReturnSelf();
        $this->itemDataBuilder->expects($this->at(1))
            ->method('addItemData')
            ->with(
                $selectedOptions[1]['label'],
                $selectedOptions[1]['value'],
                $facetedData[$selectedOptions[1]['value']]['count']
            )->willReturnSelf();
        $this->itemDataBuilder->expects($this->once())
            ->method('build')
            ->willReturn($builtData);

        $expectedFilterItems = [
            $this->createFilterItem(0, $builtData[0]['label'], $builtData[0]['value'], $builtData[0]['count']),
            $this->createFilterItem(1, $builtData[1]['label'], $builtData[1]['value'], $builtData[1]['count']),
            $this->createFilterItem(2, $builtData[2]['label'], $builtData[2]['value'], $builtData[2]['count']),
        ];

        $result = $this->target->getItems();

        $this->assertEquals($expectedFilterItems, $result);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetItemsOnlyWithResults()
    {
        $attributeCode = 'attributeCode';
        $selectedOptions = [
            [
                'label' => 'selectedOptionLabel1',
                'value' => 'selectedOptionValue1',
            ],
            [
                'label' => 'selectedOptionLabel2',
                'value' => 'selectedOptionValue2',
            ],
        ];
        $facetedData = [
            'selectedOptionValue1' => ['count' => 10],
            'selectedOptionValue2' => ['count' => 0],
        ];
        $builtData = [
            [
                'label' => $selectedOptions[0]['label'],
                'value' => $selectedOptions[0]['value'],
                'count' => $facetedData[$selectedOptions[0]['value']]['count'],
            ],
        ];

        $this->attribute->expects($this->atLeastOnce())
            ->method('getAttributeCode')
            ->willReturn($attributeCode);
        $this->attribute->expects($this->atLeastOnce())
            ->method('getIsFilterable')
            ->willReturn(AbstractFilter::ATTRIBUTE_OPTIONS_ONLY_WITH_RESULTS);
        $this->attribute->expects($this->atLeastOnce())
            ->method('getFrontend')
            ->willReturn($this->frontend);

        $this->target->setAttributeModel($this->attribute);

        $this->frontend->expects($this->once())
            ->method('getSelectOptions')
            ->willReturn($selectedOptions);

        $this->fulltextCollection->expects($this->once())
            ->method('getFacetedData')
            ->willReturn($facetedData);

        $this->itemDataBuilder->expects($this->once())
            ->method('addItemData')
            ->with(
                $selectedOptions[0]['label'],
                $selectedOptions[0]['value'],
                $facetedData[$selectedOptions[0]['value']]['count']
            )->willReturnSelf();

        $this->itemDataBuilder->expects($this->once())
            ->method('build')
            ->willReturn($builtData);

        $expectedFilterItems = [
            $this->createFilterItem(0, $builtData[0]['label'], $builtData[0]['value'], $builtData[0]['count']),
        ];
        $result = $this->target->getItems();

        $this->assertEquals($expectedFilterItems, $result);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetItemsIfFacetedDataIsEmpty()
    {
        $attributeCode = 'attributeCode';

        $this->attribute->expects($this->atLeastOnce())
            ->method('getAttributeCode')
            ->willReturn($attributeCode);
        $this->attribute->expects($this->atLeastOnce())
            ->method('getIsFilterable')
            ->willReturn(0);

        $this->target->setAttributeModel($this->attribute);

        $this->fulltextCollection->expects($this->once())
            ->method('getFacetedData')
            ->willReturn([]);

        $this->itemDataBuilder->expects($this->once())
            ->method('build')
            ->willReturn([]);

        $this->assertEquals([], $this->target->getItems());
    }

    /**
     * @param int $index
     * @param string $label
     * @param string $value
     * @param int $count
     * @return Item|MockObject
     */
    private function createFilterItem($index, $label, $value, $count)
    {
        $filterItem = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->setMethods(['setFilter', 'setLabel', 'setValue', 'setCount'])
            ->getMock();

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

        $this->filterItemFactory->expects($this->at($index))
            ->method('create')
            ->willReturn($filterItem);

        return $filterItem;
    }
}
