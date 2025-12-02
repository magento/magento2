<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductAttributeGroupRepositoryInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as EavAttribute;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory as EavAttributeFactory;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Eav;
use Magento\Eav\Api\Data\AttributeGroupInterface;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\Group;
use Magento\Eav\Model\Entity\Attribute\Source\SourceInterface;
use Magento\Eav\Model\Entity\Type as EntityType;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection as AttributeCollection;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory as AttributeCollectionFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\Collection as GroupCollection;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory as GroupCollectionFactory;
use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\Api\AttributeInterface;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Currency;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Locale\Currency as CurrencyLocale;
use Magento\Framework\Phrase;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\DataProvider\EavValidationRules;
use Magento\Ui\DataProvider\Mapper\FormElement as FormElementMapper;
use Magento\Ui\DataProvider\Mapper\MetaProperties as MetaPropertiesMapper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @method Eav getModel
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EavTest extends AbstractModifierTestCase
{
    /**
     * @var Config|MockObject
     */
    private $eavConfigMock;

    /**
     * @var EavValidationRules|MockObject
     */
    private $eavValidationRulesMock;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var GroupCollectionFactory|MockObject
     */
    private $groupCollectionFactoryMock;

    /**
     * @var GroupCollection|MockObject
     */
    private $groupCollectionMock;

    /**
     * @var Group|MockObject
     */
    private $groupMock;

    /**
     * @var EavAttribute|MockObject
     */
    private $attributeMock;

    /**
     * @var EntityType|MockObject
     */
    private $entityTypeMock;

    /**
     * @var AttributeCollectionFactory|MockObject
     */
    private $attributeCollectionFactoryMock;

    /**
     * @var AttributeCollection|MockObject
     */
    private $attributeCollectionMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var FormElementMapper|MockObject
     */
    private $formElementMapperMock;

    /**
     * @var MetaPropertiesMapper|MockObject
     */
    private $metaPropertiesMapperMock;

    /**
     * @var SearchCriteriaBuilder|MockObject
     */
    private $searchCriteriaBuilderMock;

    /**
     * @var ProductAttributeGroupRepositoryInterface|MockObject
     */
    private $attributeGroupRepositoryMock;

    /**
     * @var SearchCriteria|MockObject
     */
    private $searchCriteriaMock;
    
    /**
     * @var SearchResultsInterface|MockObject
     */
    private $attributeGroupSearchResultsMock;

    /**
     * @var SortOrderBuilder|MockObject
     */
    private $sortOrderBuilderMock;

    /**
     * @var ProductAttributeRepositoryInterface|MockObject
     */
    private $attributeRepositoryMock;

    /**
     * @var Group|MockObject
     */
    private $attributeGroupMock;

    /**
     * @var SearchResultsInterface|MockObject
     */
    private $searchResultsMock;

    /**
     * @var Attribute
     */
    private $eavAttributeMock;

    /**
     * @var StoreInterface|MockObject
     */
    protected $storeMock;

    /**
     * @var Currency|MockObject
     */
    protected $currencyMock;

    /**
     * @var CurrencyLocale|MockObject
     */
    protected $currencyLocaleMock;

    /**
     * @var Attribute
     */
    protected $productAttributeMock;

    /**
     * @var ArrayManager|MockObject
     */
    protected $arrayManagerMock;

    /**
     * @var EavAttributeFactory|MockObject
     */
    protected $eavAttributeFactoryMock;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $eventManagerMock;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var Eav
     */
    protected $eav;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = new ObjectManager($this);
        $this->eavConfigMock = $this->createMock(Config::class);
        $this->eavValidationRulesMock = $this->createMock(EavValidationRules::class);
        $this->requestMock = $this->createMock(RequestInterface::class);
        $this->groupCollectionFactoryMock = $this->createPartialMock(
            GroupCollectionFactory::class,
            ['create']
        );
        $this->groupCollectionMock = $this->createMock(GroupCollection::class);
        $this->attributeMock = $this->createMock(EavAttribute::class);
        $this->groupMock = $this->createMock(Group::class);
        $this->entityTypeMock = $this->createMock(EntityType::class);
        $this->attributeCollectionFactoryMock = $this->createPartialMock(
            AttributeCollectionFactory::class,
            ['create']
        );
        $this->attributeCollectionMock = $this->createMock(AttributeCollection::class);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->formElementMapperMock = $this->createMock(FormElementMapper::class);
        $this->metaPropertiesMapperMock = $this->createMock(MetaPropertiesMapper::class);
        $this->searchCriteriaBuilderMock = $this->createMock(SearchCriteriaBuilder::class);
        $this->attributeGroupRepositoryMock = $this->createMock(ProductAttributeGroupRepositoryInterface::class);
        $this->attributeGroupMock = $this->createMock(Group::class);
        $this->attributeRepositoryMock = $this->createMock(ProductAttributeRepositoryInterface::class);
        $this->searchCriteriaMock = $this->createMock(SearchCriteria::class);
        $this->attributeGroupSearchResultsMock = $this->createMock(SearchResultsInterface::class);
        $this->sortOrderBuilderMock = $this->createMock(SortOrderBuilder::class);
        $this->searchResultsMock = $this->createMock(SearchResultsInterface::class);
        // Use parent Attribute class - all setters work via magic methods (DataObject)
        $this->eavAttributeMock = $this->createPartialMock(Attribute::class, []);
        $this->productAttributeMock = $this->createPartialMock(Attribute::class, []);
        $this->arrayManagerMock = $this->createMock(ArrayManager::class);
        $this->eavAttributeFactoryMock = $this->createPartialMock(
            EavAttributeFactory::class,
            ['create']
        );
        $this->eventManagerMock = $this->createMock(ManagerInterface::class);

        $this->eavAttributeFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->eavAttributeMock);
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
            ->willReturn(new \ArrayIterator([$this->groupMock]));
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
        $this->storeMock = $this->createMock(StoreInterface::class);
        $this->storeManagerMock->expects($this->any())
            ->method('isSingleStoreMode')
            ->willReturn(true);

        $this->eav =$this->getModel();
    }

    /**
     * {@inheritdoc}
     */
    protected function createModel()
    {
        return $this->objectManager->getObject(
            Eav::class,
            [
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
                'attributeRepository' => $this->attributeRepositoryMock,
                'arrayManager' => $this->arrayManagerMock,
                'eavAttributeFactory' => $this->eavAttributeFactoryMock,
                '_eventManager' => $this->eventManagerMock,
                'attributeCollectionFactory' => $this->attributeCollectionFactoryMock
            ]
        );
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

        $this->attributeCollectionFactoryMock->expects($this->once())->method('create')
            ->willReturn($this->attributeCollectionMock);

        $this->attributeCollectionMock->expects($this->any())->method('getItems')
            ->willReturn([$this->eavAttributeMock]);

        $this->locatorMock->expects($this->any())->method('getProduct')
            ->willReturn($this->productMock);

        $this->productMock->setId(1);
        $this->productMock->setAttributeSetId(4);
        $this->productMock->setData(ProductAttributeInterface::CODE_PRICE, '19.9900');

        $this->searchCriteriaBuilderMock->expects($this->any())->method('addFilter')
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->any())->method('create')
            ->willReturn($this->searchCriteriaMock);
        $this->attributeGroupSearchResultsMock->method('getItems')
            ->willReturn([$this->attributeGroupMock]);
        $this->attributeGroupRepositoryMock->expects($this->any())->method('getList')
            ->willReturn($this->attributeGroupSearchResultsMock);
        $this->sortOrderBuilderMock->expects($this->once())->method('setField')
            ->willReturnSelf();
        $this->sortOrderBuilderMock->expects($this->once())->method('setAscendingDirection')
            ->willReturnSelf();
        $dataObjectMock = $this->createMock(AbstractSimpleObject::class);
        $this->sortOrderBuilderMock->expects($this->once())->method('create')
            ->willReturn($dataObjectMock);

        $this->searchCriteriaBuilderMock->expects($this->any())->method('addFilter')
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->once())->method('addSortOrder')
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->any())->method('create')
            ->willReturn($this->searchCriteriaMock);

        $this->attributeRepositoryMock->expects($this->once())->method('getList')
            ->with($this->searchCriteriaMock)
            ->willReturn($this->searchResultsMock);
        $this->eavAttributeMock->setAttributeGroupCode('product-details');
        $this->eavAttributeMock->setApplyTo([]);
        $this->eavAttributeMock->setFrontendInput('price');
        $this->eavAttributeMock->setAttributeCode(ProductAttributeInterface::CODE_PRICE);
        $this->searchResultsMock->expects($this->once())->method('getItems')
            ->willReturn([$this->eavAttributeMock]);

        $this->assertEquals($sourceData, $this->eav->modifyData([]));
    }

    /**
     * @param int|null $productId
     * @param bool $productRequired
     * @param string|null $attrValue
     * @param array $expected
     * @param bool $locked
     * @param string|null $frontendInput
     * @param array $expectedCustomize
     * @covers       \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Eav::isProductExists
     * @covers       \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Eav::setupAttributeMeta
     */
    #[DataProvider('setupAttributeMetaDataProvider')]
    public function testSetupAttributeMetaDefaultAttribute(
        $productId,
        bool $productRequired,
        $attrValue,
        array $expected,
        bool $locked = false,
        ?string $frontendInput = null,
        array $expectedCustomize = []
    ) : void {
        $configPath = 'arguments/data/config';
        $groupCode = 'product-details';
        $sortOrder = '0';
        $attributeOptions = [
            ['value' => 1, 'label' => 'Int label'],
            ['value' => 1.5, 'label' => 'Float label'],
            ['value' => true, 'label' => 'Boolean label'],
            ['value' => 'string', 'label' => 'String label'],
            ['value' => ['test1', 'test2'], 'label' => 'Array label'],
        ];
        $attributeOptionsExpected = [
            ['value' => '1', 'label' => 'Int label', '__disableTmpl' => true],
            ['value' => '1.5', 'label' => 'Float label', '__disableTmpl' => true],
            ['value' => '1', 'label' => 'Boolean label', '__disableTmpl' => true],
            ['value' => 'string', 'label' => 'String label', '__disableTmpl' => true],
            ['value' => ['test1', 'test2'], 'label' => 'Array label', '__disableTmpl' => true],
        ];

        $this->productMock->setId($productId);
        $this->productMock->setLockedAttribute('code', $locked);
        $this->productAttributeMock->setIsRequired($productRequired);
        $this->productAttributeMock->setDefaultValue('required_value');
        $this->productAttributeMock->setAttributeCode('code');
        $this->productAttributeMock->setValue('value');
        $this->productAttributeMock->setFrontendInput($frontendInput);

        $attributeMock = $this->createStub(AttributeInterface::class);
        $attributeMock->method('getValue')->willReturn($attrValue);

        $this->productMock->setCustomAttribute('code', $attributeMock);
        $this->eavAttributeMock->setUsesSource(true);

        $attributeSource = $this->createStub(SourceInterface::class);
        $attributeSource->method('getAllOptions')->willReturn($attributeOptions);

        $this->eavAttributeMock->setSource($attributeSource);

        $this->arrayManagerMock->method('set')
            ->with(
                $configPath,
                [],
                $expected
            )
            ->willReturn($expected);

        $this->arrayManagerMock->expects($this->any())
            ->method('merge')
            ->with(
                $this->anything(),
                $this->anything(),
                $this->callback(
                    function ($value) use ($attributeOptionsExpected) {
                        return isset($value['options']) ? $value['options'] === $attributeOptionsExpected : true;
                    }
                )
            )
            ->willReturn($expected + $expectedCustomize);

        $this->arrayManagerMock->method('get')->willReturn([]);
        $this->arrayManagerMock->method('exists')->willReturn(true);

        $actual = $this->eav->setupAttributeMeta($this->productAttributeMock, $groupCode, $sortOrder);

        $this->assertEquals(
            $expected + $expectedCustomize,
            $actual
        );
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function setupAttributeMetaDataProvider()
    {
        return [
            'default_null_prod_not_new_and_required' => [
                'productId' => 1,
                'productRequired' => true,
                'attrValue' => 'val',
                'expected' => [
                    'dataType' => null,
                    'formElement' => null,
                    'visible' => null,
                    'required' => true,
                    'notice' => null,
                    'default' => null,
                    'label' => new Phrase(null),
                    'code' => 'code',
                    'source' => 'product-details',
                    'scopeLabel' => '',
                    'globalScope' => false,
                    'sortOrder' => 0,
                ],
            ],
            'default_null_prod_not_new_locked_and_required' => [
                'productId' => 1,
                'productRequired' => true,
                'attrValue' => 'val',
                'expected' => [
                    'dataType' => null,
                    'formElement' => null,
                    'visible' => null,
                    'required' => true,
                    'notice' => null,
                    'default' => null,
                    'label' => new Phrase(null),
                    'code' => 'code',
                    'source' => 'product-details',
                    'scopeLabel' => '',
                    'globalScope' => false,
                    'sortOrder' => 0,
                ],
                'locked' => true,
            ],
            'default_null_prod_not_new_and_not_required' => [
                'productId' => 1,
                'productRequired' => false,
                'attrValue' => 'val',
                'expected' => [
                    'dataType' => null,
                    'formElement' => null,
                    'visible' => null,
                    'required' => false,
                    'notice' => null,
                    'default' => null,
                    'label' => new Phrase(null),
                    'code' => 'code',
                    'source' => 'product-details',
                    'scopeLabel' => '',
                    'globalScope' => false,
                    'sortOrder' => 0,
                ],
            ],
            'default_null_prod_new_and_not_required' => [
                'productId' => null,
                'productRequired' => false,
                'attrValue' => null,
                'expected' => [
                    'dataType' => null,
                    'formElement' => null,
                    'visible' => null,
                    'required' => false,
                    'notice' => null,
                    'default' => 'required_value',
                    'label' => new Phrase(null),
                    'code' => 'code',
                    'source' => 'product-details',
                    'scopeLabel' => '',
                    'globalScope' => false,
                    'sortOrder' => 0,
                ],
            ],
            'default_null_prod_new_locked_and_not_required' => [
                'productId' => null,
                'productRequired' => false,
                'attrValue' => null,
                'expected' => [
                    'dataType' => null,
                    'formElement' => null,
                    'visible' => null,
                    'required' => false,
                    'notice' => null,
                    'default' => 'required_value',
                    'label' => new Phrase(null),
                    'code' => 'code',
                    'source' => 'product-details',
                    'scopeLabel' => '',
                    'globalScope' => false,
                    'sortOrder' => 0,
                ],
                'locked' => true,
            ],
            'default_null_prod_new_and_required' => [
                'productId' => null,
                'productRequired' => false,
                'attrValue' => null,
                'expected' => [
                    'dataType' => null,
                    'formElement' => null,
                    'visible' => null,
                    'required' => false,
                    'notice' => null,
                    'default' => 'required_value',
                    'label' => new Phrase(null),
                    'code' => 'code',
                    'source' => 'product-details',
                    'scopeLabel' => '',
                    'globalScope' => false,
                    'sortOrder' => 0,
                ],
            ],
            'datetime_null_prod_not_new_and_required' => [
                'productId' => 1,
                'productRequired' => true,
                'attrValue' => 'val',
                'expected' => [
                    'dataType' => 'datetime',
                    'formElement' => 'datetime',
                    'visible' => null,
                    'required' => true,
                    'notice' => null,
                    'default' => null,
                    'label' => new Phrase(null),
                    'code' => 'code',
                    'source' => 'product-details',
                    'scopeLabel' => '',
                    'globalScope' => false,
                    'sortOrder' => 0,
                ],
                'locked' => false,
                'frontendInput' => 'datetime',
                'expectedCustomize' => ['arguments' => ['data' => ['config' => ['options' => ['showsTime' => 1]]]]],
            ],
        ];
    }
}
