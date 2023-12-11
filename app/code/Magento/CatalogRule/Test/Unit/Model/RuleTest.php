<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Test\Unit\Model;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogRule\Api\Data\RuleInterface;
use Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor;
use Magento\CatalogRule\Model\Rule;
use Magento\CatalogRule\Model\Rule\Condition\CombineFactory;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Model\ResourceModel\Iterator;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Rule\Model\Condition\Combine;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RuleTest extends TestCase
{
    /**
     * @var Rule
     */
    private $rule;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    /**
     * @var CombineFactory|MockObject
     */
    private $combineFactory;

    /**
     * @var Store|MockObject
     */
    private $storeModel;

    /**
     * @var Website|MockObject
     */
    private $websiteModel;

    /**
     * @var Combine|MockObject
     */
    private $condition;

    /**
     * @var RuleProductProcessor|MockObject
     */
    private $ruleProductProcessor;

    /**
     * @var CollectionFactory|MockObject
     */
    private $productCollectionFactory;

    /**
     * @var Iterator|MockObject
     */
    private $resourceIterator;

    /**
     * @var Product|MockObject
     */
    private $productModel;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->storeModel = $this->createPartialMock(Store::class, ['__wakeup', 'getId']);
        $this->combineFactory = $this->createPartialMock(
            CombineFactory::class,
            [
                'create'
            ]
        );
        $this->productModel = $this->createPartialMock(
            Product::class,
            [
                '__wakeup',
                'getId',
                'setData'
            ]
        );
        $this->condition = $this->getMockBuilder(Combine::class)
            ->addMethods(['setRule'])
            ->onlyMethods(['validate'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->websiteModel = $this->createPartialMock(
            Website::class,
            [
                '__wakeup',
                'getId',
                'getDefaultStore'
            ]
        );
        $this->ruleProductProcessor = $this->createMock(
            RuleProductProcessor::class
        );

        $this->productCollectionFactory = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );

        $this->resourceIterator = $this->createPartialMock(
            Iterator::class,
            ['walk']
        );

        $extensionFactoryMock = $this->createMock(ExtensionAttributesFactory::class);
        $attributeValueFactoryMock = $this->createMock(AttributeValueFactory::class);

        $this->rule = $this->objectManager->getObject(
            Rule::class,
            [
                'storeManager' => $this->storeManager,
                'combineFactory' => $this->combineFactory,
                'ruleProductProcessor' => $this->ruleProductProcessor,
                'productCollectionFactory' => $this->productCollectionFactory,
                'resourceIterator' => $this->resourceIterator,
                'extensionFactory' => $extensionFactoryMock,
                'customAttributeFactory' => $attributeValueFactoryMock,
                'serializer' => $this->getSerializerMock()
            ]
        );
    }

    /**
     * Get mock for serializer.
     *
     * @return MockObject
     */
    private function getSerializerMock(): MockObject
    {
        $serializerMock = $this->getMockBuilder(Json::class)->disableOriginalConstructor()
            ->onlyMethods(['serialize', 'unserialize'])
            ->getMock();

        $serializerMock->expects($this->any())
            ->method('serialize')
            ->willReturnCallback(
                function ($value) {
                    return json_encode($value);
                }
            );

        $serializerMock->expects($this->any())
            ->method('unserialize')
            ->willReturnCallback(
                function ($value) {
                    return json_decode($value, true);
                }
            );

        return $serializerMock;
    }

    /**
     * @param bool $validate
     *
     * @return void
     * @dataProvider dataProviderCallbackValidateProduct
     */
    public function testCallbackValidateProduct($validate): void
    {
        $args['product'] = $this->productModel;
        $args['attributes'] = [];
        $args['idx'] = 0;
        $args['row'] = [
            'entity_id' => '1',
            'entity_type_id' => '4',
            'attribute_set_id' => '4',
            'type_id' => 'simple',
            'sku' => 'Product',
            'has_options' => '0',
            'required_options' => '0',
            'created_at' => '2014-06-25 13:14:30',
            'updated_at' => '2014-06-25 14:37:15'
        ];
        $this->storeManager->expects($this->any())->method('getWebsites')->with(false)
            ->willReturn([$this->websiteModel, $this->websiteModel, $this->websiteModel]);
        $this->websiteModel
            ->method('getId')
            ->willReturnOnConsecutiveCalls('1', '2', '3');
        $this->websiteModel->expects($this->any())->method('getDefaultStore')
            ->willReturn($this->storeModel);
        $this->storeModel
            ->method('getId')
            ->willReturnOnConsecutiveCalls('1', '2', '3');
        $this->combineFactory->expects($this->any())->method('create')
            ->willReturn($this->condition);
        $this->condition->expects($this->any())->method('validate')
            ->willReturn($validate);
        $this->condition->expects($this->any())->method('setRule')->willReturnSelf();
        $this->productModel->expects($this->any())->method('getId')
            ->willReturn(1);

        $this->rule->setWebsiteIds('1,2');
        $this->rule->callbackValidateProduct($args);

        $matchingProducts = $this->rule->getMatchingProductIds();
        foreach ($matchingProducts['1'] as $matchingRules) {
            $this->assertEquals($validate, $matchingRules);
        }
        $this->assertNull($matchingProducts['1']['3'] ?? null);
    }

    /**
     * Data provider for callbackValidateProduct test.
     *
     * @return array
     */
    public function dataProviderCallbackValidateProduct(): array
    {
        return [
            [false],
            [true]
        ];
    }

    /**
     * Test validateData action.
     *
     * @param array $data Data for the rule actions
     * @param bool|array $expected True or an array of errors
     *
     * @return void
     * @dataProvider validateDataDataProvider
     */
    public function testValidateData(array $data, $expected): void
    {
        $result = $this->rule->validateData(new DataObject($data));
        $this->assertEquals($result, $expected);
    }

    /**
     * Data provider for testValidateData test.
     *
     * @return array
     */
    public function validateDataDataProvider(): array
    {
        return [
            [
                [
                    'simple_action' => 'by_fixed',
                    'discount_amount' => '123'
                ],
                true
            ],
            [
                [
                    'simple_action' => 'by_percent',
                    'discount_amount' => '9.99'
                ],
                true
            ],
            [
                [
                    'simple_action' => 'by_percent',
                    'discount_amount' => '123.12'
                ],
                [
                    'Percentage discount should be between 0 and 100.'
                ]
            ],
            [
                [
                    'simple_action' => 'to_percent',
                    'discount_amount' => '-12'
                ],
                [
                    'Percentage discount should be between 0 and 100.'
                ]
            ],
            [
                [
                    'simple_action' => 'to_fixed',
                    'discount_amount' => '-1234567890'
                ],
                [
                    'Discount value should be 0 or greater.'
                ]
            ],
            [
                [
                    'simple_action' => 'invalid action',
                    'discount_amount' => '12'
                ],
                [
                    'Unknown action.'
                ]
            ]
        ];
    }

    /**
     * Test after delete action.
     *
     * @return void
     */
    public function testAfterDelete(): void
    {
        $indexer = $this->getMockForAbstractClass(IndexerInterface::class);
        $indexer->expects($this->once())->method('invalidate');
        $this->ruleProductProcessor->expects($this->once())->method('getIndexer')->willReturn($indexer);
        $this->rule->afterDelete();
    }

    /**
     * Test after update action for active and deactivated rule.
     *
     * @param int $active
     *
     * @return void
     * @dataProvider afterUpdateDataProvider
     */
    public function testAfterUpdate(int $active): void
    {
        $this->rule->isObjectNew(false);
        $this->rule->setIsActive($active);
        $this->rule->setOrigData(RuleInterface::IS_ACTIVE, 1);
        $indexer = $this->getMockForAbstractClass(IndexerInterface::class);
        $indexer->expects($this->once())->method('invalidate');
        $this->ruleProductProcessor->expects($this->once())->method('getIndexer')->willReturn($indexer);
        $this->rule->afterSave();
    }

    /**
     * Test after update action for inactive rule.
     *
     * @return void
     */
    public function testAfterUpdateInactiveRule(): void
    {
        $this->rule->isObjectNew(false);
        $this->rule->setIsActive(0);
        $this->rule->setOrigData(RuleInterface::IS_ACTIVE, 0);
        $this->ruleProductProcessor->expects($this->never())->method('getIndexer');
        $this->rule->afterSave();
    }

    /**
     * @return array
     */
    public function afterUpdateDataProvider(): array
    {
        return [
            ['active' => 0],
            ['active' => 1]
        ];
    }

    /**
     * Test isRuleBehaviorChanged action
     *
     * @param array $dataArray
     * @param array $originDataArray
     * @param bool $isObjectNew
     * @param bool $result
     *
     * @return void
     * @dataProvider isRuleBehaviorChangedDataProvider
     */
    public function testIsRuleBehaviorChanged(
        array $dataArray,
        array $originDataArray,
        bool $isObjectNew,
        bool $result
    ): void {
        $this->rule->setData('website_ids', []);
        $this->rule->isObjectNew($isObjectNew);
        $indexer = $this->getMockForAbstractClass(IndexerInterface::class);
        $indexer->expects($this->any())->method('invalidate');
        $this->ruleProductProcessor->expects($this->any())->method('getIndexer')->willReturn($indexer);

        foreach ($dataArray as $data) {
            $this->rule->setData($data);
        }
        $this->rule->afterSave();

        foreach ($originDataArray as $data) {
            $this->rule->setOrigData($data);
        }
        $this->assertEquals($result, $this->rule->isRuleBehaviorChanged());
    }

    /**
     * Data provider for testIsRuleBehaviorChanged test.
     *
     * @return array
     */
    public function isRuleBehaviorChangedDataProvider(): array
    {
        return [
            [['new name', 'new description'], ['name', 'description'], false, false],
            [['name', 'description'], ['name', 'description'], false, false],
            [['name', 'important_data'], ['name', 'important_data'], false, false],
            [['name', 'new important_data'], ['name', 'important_data'], false, true],
            [['name', 'description'], ['name', 'description'], true, true],
            [['name', 'description'], ['name', 'important_data'], true, true]
        ];
    }

    /**
     * @return void
     */
    public function testGetConditionsFieldSetId(): void
    {
        $formName = 'form_name';
        $this->rule->setId(100);
        $expectedResult = 'form_namerule_conditions_fieldset_100';
        $this->assertEquals($expectedResult, $this->rule->getConditionsFieldSetId($formName));
    }

    /**
     * @return void
     */
    public function testReindex(): void
    {
        $this->ruleProductProcessor->expects($this->once())->method('reindexList');
        $this->rule->reindex();
    }
}
