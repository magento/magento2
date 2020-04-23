<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Test\Unit\Model;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RuleTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\CatalogRule\Model\Rule */
    protected $rule;

    /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager */
    private $objectManager;

    /** @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $storeManager;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $combineFactory;

    /** @var \Magento\Store\Model\Store|\PHPUnit_Framework_MockObject_MockObject */
    protected $storeModel;

    /** @var \Magento\Store\Model\Website|\PHPUnit_Framework_MockObject_MockObject */
    protected $websiteModel;

    /** @var \Magento\Rule\Model\Condition\Combine|\PHPUnit_Framework_MockObject_MockObject */
    protected $condition;

    /**
     * @var \Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_ruleProductProcessor;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_productCollectionFactory;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Iterator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_resourceIterator;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productModel;

    /**
     * Set up before test
     *
     * @return void
     */
    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->storeManager = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->storeModel = $this->createPartialMock(\Magento\Store\Model\Store::class, ['__wakeup', 'getId']);
        $this->combineFactory = $this->createPartialMock(
            \Magento\CatalogRule\Model\Rule\Condition\CombineFactory::class,
            [
                'create'
            ]
        );
        $this->productModel = $this->createPartialMock(
            \Magento\Catalog\Model\Product::class,
            [
                '__wakeup',
                'getId',
                'setData'
            ]
        );
        $this->condition = $this->createPartialMock(
            \Magento\Rule\Model\Condition\Combine::class,
            [
                'setRule',
                'validate'
            ]
        );
        $this->websiteModel = $this->createPartialMock(
            \Magento\Store\Model\Website::class,
            [
                '__wakeup',
                'getId',
                'getDefaultStore'
            ]
        );
        $this->_ruleProductProcessor = $this->createMock(
            \Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor::class
        );

        $this->_productCollectionFactory = $this->createPartialMock(
            \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class,
            ['create']
        );

        $this->_resourceIterator = $this->createPartialMock(
            \Magento\Framework\Model\ResourceModel\Iterator::class,
            ['walk']
        );

        $extensionFactoryMock = $this->createMock(\Magento\Framework\Api\ExtensionAttributesFactory::class);
        $attributeValueFactoryMock = $this->createMock(\Magento\Framework\Api\AttributeValueFactory::class);

        $this->rule = $this->objectManager->getObject(
            \Magento\CatalogRule\Model\Rule::class,
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
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getSerializerMock()
    {
        $serializerMock = $this->getMockBuilder(\Magento\Framework\Serialize\Serializer\Json::class)
            ->disableOriginalConstructor()
            ->setMethods(['serialize', 'unserialize'])
            ->getMock();

        $serializerMock->expects($this->any())
            ->method('serialize')
            ->will(
                $this->returnCallback(
                    function ($value) {
                        return json_encode($value);
                    }
                )
            );

        $serializerMock->expects($this->any())
            ->method('unserialize')
            ->will(
                $this->returnCallback(
                    function ($value) {
                        return json_decode($value, true);
                    }
                )
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
            ->will($this->returnValue([$this->websiteModel, $this->websiteModel]));
        $this->websiteModel->expects($this->at(0))->method('getId')
            ->will($this->returnValue('1'));
        $this->websiteModel->expects($this->at(2))->method('getId')
            ->will($this->returnValue('2'));
        $this->websiteModel->expects($this->any())->method('getDefaultStore')
            ->will($this->returnValue($this->storeModel));
        $this->storeModel->expects($this->at(0))->method('getId')
            ->will($this->returnValue('1'));
        $this->storeModel->expects($this->at(1))->method('getId')
            ->will($this->returnValue('2'));
        $this->combineFactory->expects($this->any())->method('create')
            ->will($this->returnValue($this->condition));
        $this->condition->expects($this->any())->method('validate')
            ->will($this->returnValue($validate));
        $this->condition->expects($this->any())->method('setRule')
            ->will($this->returnSelf());
        $this->productModel->expects($this->any())->method('getId')
            ->will($this->returnValue(1));

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
        $result = $this->rule->validateData(new \Magento\Framework\DataObject($data));
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
        $indexer = $this->createMock(\Magento\Framework\Indexer\IndexerInterface::class);
        $indexer->expects($this->once())->method('invalidate');
        $this->_ruleProductProcessor->expects($this->once())->method('getIndexer')->will($this->returnValue($indexer));
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
        $indexer = $this->createMock(\Magento\Framework\Indexer\IndexerInterface::class);
        $indexer->expects($this->once())->method('invalidate');
        $this->_ruleProductProcessor->expects($this->once())->method('getIndexer')->will($this->returnValue($indexer));
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
        $indexer = $this->createMock(\Magento\Framework\Indexer\IndexerInterface::class);
        $indexer->expects($this->any())->method('invalidate');
        $this->_ruleProductProcessor->expects($this->any())->method('getIndexer')->will($this->returnValue($indexer));

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
