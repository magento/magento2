<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Eav;
use Magento\Eav\Model\Config;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\DataProvider\EavValidationRules;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\Collection as GroupCollection;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory as GroupCollectionFactory;
use Magento\Eav\Model\Entity\Attribute\Group;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as EavAttribute;
use Magento\Eav\Model\Entity\Type as EntityType;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection as AttributeCollection;
use Magento\Ui\DataProvider\Mapper\FormElement as FormElementMapper;
use Magento\Ui\DataProvider\Mapper\MetaProperties as MetaPropertiesMapper;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Catalog\Api\ProductAttributeGroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Api\Data\AttributeGroupInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;

/**
 * Class EavTest
 *
 * @method Eav getModel
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EavTest extends AbstractModifierTest
{
    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eavConfigMock;

    /**
     * @var EavValidationRules|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eavValidationRulesMock;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var GroupCollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $groupCollectionFactoryMock;

    /**
     * @var GroupCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $groupCollectionMock;

    /**
     * @var Group|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $groupMock;

    /**
     * @var EavAttribute|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeMock;

    /**
     * @var EntityType|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityTypeMock;

    /**
     * @var AttributeCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeCollectionMock;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var FormElementMapper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $formElementMapperMock;

    /**
     * @var MetaPropertiesMapper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $metaPropertiesMapperMock;

    /**
     * @var SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchCriteriaBuilderMock;

    /**
     * @var ProductAttributeGroupRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeGroupRepositoryMock;

    /**
     * @var SearchCriteria|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchCriteriaMock;

    /**
     * @var SortOrderBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sortOrderBuilderMock;

    /**
     * @var ProductAttributeRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeRepositoryMock;

    /**
     * @var AttributeGroupInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeGroupMock;

    /**
     * @var SearchResultsInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchResultsMock;

    /**
     * @var Attribute|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eavAttributeMock;

    protected function setUp()
    {
        parent::setUp();
        $this->eavConfigMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->eavValidationRulesMock = $this->getMockBuilder(EavValidationRules::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->getMockForAbstractClass();
        $this->groupCollectionFactoryMock = $this->getMockBuilder(GroupCollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->groupCollectionMock =
            $this->getMockBuilder(GroupCollection::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->attributeMock = $this->getMockBuilder(EavAttribute::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->groupMock = $this->getMockBuilder(Group::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAttributeGroupCode'])
            ->getMock();
        $this->entityTypeMock = $this->getMockBuilder(EntityType::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeCollectionMock = $this->getMockBuilder(AttributeCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMockForAbstractClass();
        $this->formElementMapperMock = $this->getMockBuilder(FormElementMapper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->metaPropertiesMapperMock = $this->getMockBuilder(MetaPropertiesMapper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeGroupRepositoryMock = $this->getMockBuilder(ProductAttributeGroupRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->attributeGroupMock = $this->getMockBuilder(AttributeGroupInterface::class)
            ->setMethods(['getAttributeGroupCode', 'getApplyTo'])
            ->getMockForAbstractClass();
        $this->attributeRepositoryMock = $this->getMockBuilder(ProductAttributeRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->setMethods(['getItems'])
            ->getMock();
        $this->sortOrderBuilderMock = $this->getMockBuilder(SortOrderBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchResultsMock = $this->getMockBuilder(SearchResultsInterface::class)
            ->getMockForAbstractClass();
        $this->eavAttributeMock = $this->getMockBuilder(Attribute::class)
            ->setMethods(['getAttributeGroupCode', 'getApplyTo', 'getFrontendInput', 'getAttributeCode'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->groupCollectionFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->groupCollectionMock);
        $this->groupCollectionMock->expects($this->any())
            ->method('setAttributeSetFilter')
            ->willReturnSelf();
        $this->groupCollectionMock->expects($this->any())
            ->method('setSortOrder')
            ->willReturnSelf();
        $this->groupCollectionMock->expects($this->any())
            ->method('load')
            ->willReturnSelf();
        $this->groupCollectionMock->expects($this->any())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([
                $this->groupMock,
            ]));
        $this->attributeCollectionMock->expects($this->any())
            ->method('addFieldToSelect')
            ->willReturnSelf();
        $this->attributeCollectionMock->expects($this->any())
            ->method('load')
            ->willReturnSelf();
        $this->eavConfigMock->expects($this->any())
            ->method('getEntityType')
            ->willReturn($this->entityTypeMock);
        $this->entityTypeMock->expects($this->any())
            ->method('getAttributeCollection')
            ->willReturn($this->attributeCollectionMock);
        $this->productMock->expects($this->any())
            ->method('getAttributes')
            ->willReturn([
                $this->attributeMock,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function createModel()
    {
        return $this->objectManager->getObject(Eav::class, [
            'locator' => $this->locatorMock,
            'eavValidationRules' => $this->eavValidationRulesMock,
            'eavConfig' => $this->eavConfigMock,
            'request' => $this->requestMock,
            'groupCollectionFactory' => $this->groupCollectionFactoryMock,
            'storeManager' => $this->storeManagerMock,
            'formElementMapper' => $this->formElementMapperMock,
            'metaPropertiesMapper' => $this->metaPropertiesMapperMock,
            'searchCriteriaBuilder' => $this->searchCriteriaBuilderMock,
            'attributeGroupRepository' => $this->attributeGroupRepositoryMock,
            'sortOrderBuilder' => $this->sortOrderBuilderMock,
            'attributeRepository' => $this->attributeRepositoryMock
        ]);
    }

    public function testModifyData()
    {
        $sourceData = [
            '1' => [
                'product' => [
                    ProductAttributeInterface::CODE_PRICE => '19.99'
                ]
            ]
        ];

        $this->locatorMock->expects($this->any())
            ->method('getProduct')
            ->willReturn($this->productMock);

        $this->productMock->expects($this->any())
            ->method('getId')
            ->willReturn(1);
        $this->productMock->expects($this->any())
            ->method('getAttributeSetId')
            ->willReturn(4);
        $this->productMock->expects($this->any())
            ->method('getData')
            ->with(ProductAttributeInterface::CODE_PRICE)->willReturn('19.9900');

        $this->searchCriteriaBuilderMock->expects($this->any())
            ->method('addFilter')
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->any())
            ->method('create')
            ->willReturn($this->searchCriteriaMock);
        $this->attributeGroupRepositoryMock->expects($this->any())
            ->method('getList')
            ->willReturn($this->searchCriteriaMock);
        $this->searchCriteriaMock->expects($this->any())
            ->method('getItems')
            ->willReturn([$this->attributeGroupMock]);
        $this->sortOrderBuilderMock->expects($this->any())
            ->method('setField')
            ->willReturnSelf();
        $this->sortOrderBuilderMock->expects($this->any())
            ->method('setAscendingDirection')
            ->willReturnSelf();
        $dataObjectMock = $this->getMock('\Magento\Framework\Api\AbstractSimpleObject', [], [], '', false);
        $this->sortOrderBuilderMock->expects($this->any())
            ->method('create')
            ->willReturn($dataObjectMock);

        $this->searchCriteriaBuilderMock->expects($this->any())
            ->method('addFilter')
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->any())
            ->method('addSortOrder')
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->any())
            ->method('create')
            ->willReturn($this->searchCriteriaMock);

        $this->attributeRepositoryMock->expects($this->any())
            ->method('getList')
            ->with($this->searchCriteriaMock)
            ->willReturn($this->searchResultsMock);
        $this->eavAttributeMock->expects($this->any())
            ->method('getAttributeGroupCode')
            ->willReturn('product-details');
        $this->eavAttributeMock->expects($this->any())
            ->method('getApplyTo')
            ->willReturn([]);
        $this->eavAttributeMock->expects($this->any())
            ->method('getFrontendInput')
            ->willReturn(ProductAttributeInterface::CODE_PRICE);
        $this->eavAttributeMock->expects($this->any())
            ->method('getAttributeCode')
            ->willReturn(ProductAttributeInterface::CODE_PRICE);
        $this->searchResultsMock->expects($this->any())
            ->method('getItems')
            ->willReturn([$this->eavAttributeMock]);

        $this->assertEquals($sourceData, $this->getModel()->modifyData([]));
    }
}
