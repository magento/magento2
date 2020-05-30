<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Test\Unit\Model;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
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
    /** @var Rule */
    protected $rule;

    /** @var ObjectManager */
    private $objectManager;

    /** @var StoreManagerInterface|MockObject */
    protected $storeManager;

    /** @var MockObject */
    protected $combineFactory;

    /** @var Store|MockObject */
    protected $storeModel;

    /** @var Website|MockObject */
    protected $websiteModel;

    /** @var Combine|MockObject */
    protected $condition;

    /**
     * @var RuleProductProcessor|MockObject
     */
    protected $_ruleProductProcessor;

    /**
     * @var CollectionFactory|MockObject
     */
    protected $_productCollectionFactory;

    /**
     * @var Iterator|MockObject
     */
    protected $_resourceIterator;

    /**
     * @var Product|MockObject
     */
    protected $productModel;

    /**
     * Set up before test
     *
     * @return void
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
        $this->_ruleProductProcessor = $this->createMock(
            RuleProductProcessor::class
        );

        $this->_productCollectionFactory = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );

        $this->_resourceIterator = $this->createPartialMock(
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
                'ruleProductProcessor' => $this->_ruleProductProcessor,
                'productCollectionFactory' => $this->_productCollectionFactory,
                'resourceIterator' => $this->_resourceIterator,
                'extensionFactory' => $extensionFactoryMock,
                'customAttributeFactory' => $attributeValueFactoryMock,
                'serializer' => $this->getSerializerMock(),
            ]
        );
    }

    /**
     * Get mock for serializer
     *
     * @return MockObject
     */
    private function getSerializerMock()
    {
        $serializerMock = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->setMethods(['serialize', 'unserialize'])
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
     * @dataProvider dataProviderCallbackValidateProduct
     * @param bool $validate
     *
     * @return void
     */
    public function testCallbackValidateProduct($validate)
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
            ->willReturn([$this->websiteModel, $this->websiteModel]);
        $this->websiteModel->expects($this->at(0))->method('getId')
            ->willReturn('1');
        $this->websiteModel->expects($this->at(2))->method('getId')
            ->willReturn('2');
        $this->websiteModel->expects($this->any())->method('getDefaultStore')
            ->willReturn($this->storeModel);
        $this->storeModel->expects($this->at(0))->method('getId')
            ->willReturn('1');
        $this->storeModel->expects($this->at(1))->method('getId')
            ->willReturn('2');
        $this->combineFactory->expects($this->any())->method('create')
            ->willReturn($this->condition);
        $this->condition->expects($this->any())->method('validate')
            ->willReturn($validate);
        $this->condition->expects($this->any())->method('setRule')->willReturnSelf();
        $this->productModel->expects($this->any())->method('getId')
            ->willReturn(1);

        $this->rule->callbackValidateProduct($args);

        $matchingProducts = $this->rule->getMatchingProductIds();
        foreach ($matchingProducts['1'] as $matchingRules) {
            $this->assertEquals($validate, $matchingRules);
        }
    }

    /**
     * Data provider for callbackValidateProduct test
     *
     * @return array
     */
    public function dataProviderCallbackValidateProduct()
    {
        return [
            [false],
            [true],
        ];
    }

    /**
     * Test validateData action
     *
     * @dataProvider validateDataDataProvider
     * @param array $data Data for the rule actions
     * @param bool|array $expected True or an array of errors
     *
     * @return void
     */
    public function testValidateData($data, $expected)
    {
        $result = $this->rule->validateData(new DataObject($data));
        $this->assertEquals($result, $expected);
    }

    /**
     * Data provider for testValidateData test
     *
     * @return array
     */
    public function validateDataDataProvider()
    {
        return [
            [
                [
                    'simple_action' => 'by_fixed',
                    'discount_amount' => '123',
                ],
                true
            ],
            [
                [
                    'simple_action' => 'by_percent',
                    'discount_amount' => '9,99',
                ],
                true
            ],
            [
                [
                    'simple_action' => 'by_percent',
                    'discount_amount' => '123.12',
                ],
                [
                    'Percentage discount should be between 0 and 100.',
                ]
            ],
            [
                [
                    'simple_action' => 'to_percent',
                    'discount_amount' => '-12',
                ],
                [
                    'Percentage discount should be between 0 and 100.',
                ]
            ],
            [
                [
                    'simple_action' => 'to_fixed',
                    'discount_amount' => '-1234567890',
                ],
                [
                    'Discount value should be 0 or greater.',
                ]
            ],
            [
                [
                    'simple_action' => 'invalid action',
                    'discount_amount' => '12',
                ],
                [
                    'Unknown action.',
                ]
            ],
        ];
    }

    /**
     * Test after delete action
     *
     * @return void
     */
    public function testAfterDelete()
    {
        $indexer = $this->getMockForAbstractClass(IndexerInterface::class);
        $indexer->expects($this->once())->method('invalidate');
        $this->_ruleProductProcessor->expects($this->once())->method('getIndexer')->willReturn($indexer);
        $this->rule->afterDelete();
    }

    /**
     * Test after update action for inactive rule
     *
     * @return void
     */
    public function testAfterUpdateInactive()
    {
        $this->rule->isObjectNew(false);
        $this->rule->setIsActive(0);
        $this->_ruleProductProcessor->expects($this->never())->method('getIndexer');
        $this->rule->afterSave();
    }

    /**
     * Test after update action for active rule
     *
     * @return void
     */
    public function testAfterUpdateActive()
    {
        $this->rule->isObjectNew(false);
        $this->rule->setIsActive(1);
        $indexer = $this->getMockForAbstractClass(IndexerInterface::class);
        $indexer->expects($this->once())->method('invalidate');
        $this->_ruleProductProcessor->expects($this->once())->method('getIndexer')->willReturn($indexer);
        $this->rule->afterSave();
    }

    /**
     * Test isRuleBehaviorChanged action
     *
     * @dataProvider isRuleBehaviorChangedDataProvider
     *
     * @param array $dataArray
     * @param array $originDataArray
     * @param bool $isObjectNew
     * @param bool $result
     *
     * @return void
     */
    public function testIsRuleBehaviorChanged($dataArray, $originDataArray, $isObjectNew, $result)
    {
        $this->rule->setData('website_ids', []);
        $this->rule->isObjectNew($isObjectNew);
        $indexer = $this->getMockForAbstractClass(IndexerInterface::class);
        $indexer->expects($this->any())->method('invalidate');
        $this->_ruleProductProcessor->expects($this->any())->method('getIndexer')->willReturn($indexer);

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
     * Data provider for testIsRuleBehaviorChanged test
     *
     * @return array
     */
    public function isRuleBehaviorChangedDataProvider()
    {
        return [
            [['new name', 'new description'], ['name', 'description'], false, false],
            [['name', 'description'], ['name', 'description'], false, false],
            [['name', 'important_data'], ['name', 'important_data'], false, false],
            [['name', 'new important_data'], ['name', 'important_data'], false, true],
            [['name', 'description'], ['name', 'description'], true, true],
            [['name', 'description'], ['name', 'important_data'], true, true],
        ];
    }

    public function testGetConditionsFieldSetId()
    {
        $formName = 'form_name';
        $this->rule->setId(100);
        $expectedResult = 'form_namerule_conditions_fieldset_100';
        $this->assertEquals($expectedResult, $this->rule->getConditionsFieldSetId($formName));
    }

    public function testReindex()
    {
        $this->_ruleProductProcessor->expects($this->once())->method('reindexList');
        $this->rule->reindex();
    }
}
