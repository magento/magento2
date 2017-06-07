<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Unit\Model\Layer\Filter;

use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AttributeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogSearch\Model\Layer\Filter\Attribute
     */
    private $target;

    /** @var  \Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend|MockObject */
    private $frontend;

    /** @var  \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection|MockObject */
    private $fulltextCollection;

    /** @var  \Magento\Catalog\Model\Layer\State|MockObject */
    private $state;

    /** @var  \Magento\Eav\Model\Entity\Attribute|MockObject */
    private $attribute;

    /** @var \Magento\Framework\App\RequestInterface|MockObject */
    private $request;

    /** @var  \Magento\Catalog\Model\ResourceModel\Layer\Filter\AttributeFactory|MockObject */
    private $filterAttributeFactory;

    /** @var  \Magento\Catalog\Model\Layer\Filter\ItemFactory|MockObject */
    private $filterItemFactory;

    /** @var  \Magento\Store\Model\StoreManagerInterface|MockObject */
    private $storeManager;

    /** @var  \Magento\Catalog\Model\Layer|MockObject */
    private $layer;

    /** @var  \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder|MockObject */
    private $itemDataBuilder;

    protected function setUp()
    {
        /** @var \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory */
        $this->filterItemFactory = $this->getMockBuilder(\Magento\Catalog\Model\Layer\Filter\ItemFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        /** @var \Magento\Store\Model\StoreManagerInterface $storeManager */
        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();
        /** @var \Magento\Catalog\Model\Layer $layer */
        $this->layer = $this->getMockBuilder(\Magento\Catalog\Model\Layer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getState', 'getProductCollection'])
            ->getMock();
        $this->fulltextCollection =
            $this->getMockBuilder(\Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(['addFieldToFilter', 'getFacetedData', 'getSize'])
            ->getMock();
        $this->layer->expects($this->atLeastOnce())
            ->method('getProductCollection')
            ->will($this->returnValue($this->fulltextCollection));
        /** @var \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder */
        $this->itemDataBuilder = $this->getMockBuilder(\Magento\Catalog\Model\Layer\Filter\Item\DataBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['addItemData', 'build'])
            ->getMock();

        $this->filterAttributeFactory = $this->getMockBuilder(
            \Magento\Catalog\Model\ResourceModel\Layer\Filter\AttributeFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->state = $this->getMockBuilder(\Magento\Catalog\Model\Layer\State::class)
            ->disableOriginalConstructor()
            ->setMethods(['addFilter'])
            ->getMock();
        $this->layer->expects($this->any())
            ->method('getState')
            ->will($this->returnValue($this->state));

        $this->frontend = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOption', 'getSelectOptions'])
            ->getMock();
        $this->attribute = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAttributeCode', 'getFrontend', 'getIsFilterable'])
            ->getMock();

        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->setMethods(['getParam'])
            ->getMockForAbstractClass();

        $stripTagsFilter = $this->getMockBuilder(\Magento\Framework\Filter\StripTags::class)
            ->disableOriginalConstructor()
            ->setMethods(['filter'])
            ->getMock();
        $stripTagsFilter->expects($this->any())
            ->method('filter')
            ->will($this->returnArgument(0));

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->target = $objectManagerHelper->getObject(
            \Magento\CatalogSearch\Model\Layer\Filter\Attribute::class,
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

    public function testApplyFilter()
    {
        $attributeCode = 'attributeCode';
        $attributeValue = 'attributeValue';
        $attributeLabel = 'attributeLabel';

        $this->attribute->expects($this->exactly(2))
            ->method('getAttributeCode')
            ->will($this->returnValue($attributeCode));
        $this->attribute->expects($this->atLeastOnce())
            ->method('getFrontend')
            ->will($this->returnValue($this->frontend));

        $this->target->setAttributeModel($this->attribute);

        $this->request->expects($this->once())
            ->method('getParam')
            ->with($attributeCode)
            ->will($this->returnValue($attributeValue));

        $this->fulltextCollection->expects($this->once())
            ->method('addFieldToFilter')
            ->with($attributeCode, $attributeValue)
            ->will($this->returnSelf());

        $this->frontend->expects($this->once())
            ->method('getOption')
            ->with($attributeValue)
            ->will($this->returnValue($attributeLabel));

        $filterItem = $this->createFilterItem(0, $attributeLabel, $attributeValue, 0);

        $filterItem->expects($this->once())
            ->method('setFilter')
            ->with($this->target)
            ->will($this->returnSelf());

        $filterItem->expects($this->once())
            ->method('setLabel')
            ->with($attributeLabel)
            ->will($this->returnSelf());

        $filterItem->expects($this->once())
            ->method('setValue')
            ->with($attributeValue)
            ->will($this->returnSelf());

        $filterItem->expects($this->once())
            ->method('setCount')
            ->with(0)
            ->will($this->returnSelf());

        $this->state->expects($this->once())
            ->method('addFilter')
            ->with($filterItem)
            ->will($this->returnSelf());

        $result = $this->target->apply($this->request);

        $this->assertEquals($this->target, $result);
    }

    public function testGetItemsWithApply()
    {
        $attributeCode = 'attributeCode';
        $attributeValue = 'attributeValue';
        $attributeLabel = 'attributeLabel';

        $this->attribute->expects($this->exactly(2))
            ->method('getAttributeCode')
            ->will($this->returnValue($attributeCode));
        $this->attribute->expects($this->atLeastOnce())
            ->method('getFrontend')
            ->will($this->returnValue($this->frontend));

        $this->target->setAttributeModel($this->attribute);

        $this->request->expects($this->once())
            ->method('getParam')
            ->with($attributeCode)
            ->will($this->returnValue($attributeValue));

        $this->fulltextCollection->expects($this->once())
            ->method('addFieldToFilter')
            ->with($attributeCode, $attributeValue)
            ->will($this->returnSelf());

        $this->frontend->expects($this->once())
            ->method('getOption')
            ->with($attributeValue)
            ->will($this->returnValue($attributeLabel));
        $filterItem = $this->createFilterItem(0, $attributeLabel, $attributeValue, 0);

        $this->state->expects($this->once())
            ->method('addFilter')
            ->with($filterItem)
            ->will($this->returnSelf());

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
            ->will($this->returnValue($attributeCode));
        $this->attribute->expects($this->atLeastOnce())
            ->method('getFrontend')
            ->will($this->returnValue($this->frontend));

        $this->target->setAttributeModel($this->attribute);

        $this->frontend->expects($this->once())
            ->method('getSelectOptions')
            ->will($this->returnValue($selectedOptions));

        $this->fulltextCollection->expects($this->once())
            ->method('getFacetedData')
            ->will($this->returnValue($facetedData));

        $this->itemDataBuilder->expects($this->at(0))
            ->method('addItemData')
            ->with(
                $selectedOptions[0]['label'],
                $selectedOptions[0]['value'],
                $facetedData[$selectedOptions[0]['value']]['count']
            )
            ->will($this->returnSelf());
        $this->itemDataBuilder->expects($this->at(1))
            ->method('addItemData')
            ->with(
                $selectedOptions[1]['label'],
                $selectedOptions[1]['value'],
                $facetedData[$selectedOptions[1]['value']]['count']
            )
            ->will($this->returnSelf());
        $this->itemDataBuilder->expects($this->once())
            ->method('build')
            ->will($this->returnValue($builtData));

        $this->fulltextCollection->expects($this->once())
            ->method('getSize')
            ->will($this->returnValue(50));

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
            ->will($this->returnValue($this->frontend));

        $this->target->setAttributeModel($this->attribute);

        $this->frontend->expects($this->once())
            ->method('getSelectOptions')
            ->willReturn($selectedOptions);

        $this->fulltextCollection->expects($this->once())
            ->method('getFacetedData')
            ->willReturn($facetedData);
        $this->fulltextCollection->expects($this->once())
            ->method('getSize')
            ->will($this->returnValue(50));

        $this->itemDataBuilder->expects($this->once())
            ->method('addItemData')
            ->with(
                $selectedOptions[0]['label'],
                $selectedOptions[0]['value'],
                $facetedData[$selectedOptions[0]['value']]['count']
            )
            ->will($this->returnSelf());

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
     * @return \Magento\Catalog\Model\Layer\Filter\Item|MockObject
     */
    private function createFilterItem($index, $label, $value, $count)
    {
        $filterItem = $this->getMockBuilder(\Magento\Catalog\Model\Layer\Filter\Item::class)
            ->disableOriginalConstructor()
            ->setMethods(['setFilter', 'setLabel', 'setValue', 'setCount'])
            ->getMock();

        $filterItem->expects($this->once())
            ->method('setFilter')
            ->with($this->target)
            ->will($this->returnSelf());

        $filterItem->expects($this->once())
            ->method('setLabel')
            ->with($label)
            ->will($this->returnSelf());

        $filterItem->expects($this->once())
            ->method('setValue')
            ->with($value)
            ->will($this->returnSelf());

        $filterItem->expects($this->once())
            ->method('setCount')
            ->with($count)
            ->will($this->returnSelf());

        $this->filterItemFactory->expects($this->at($index))
            ->method('create')
            ->will($this->returnValue($filterItem));

        return $filterItem;
    }
}
