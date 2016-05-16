<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class RuleTest
 * @package Magento\CatalogRule\Test\Unit\Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RuleTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\CatalogRule\Model\Rule */
    protected $rule;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

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
        $this->storeManager = $this->getMock('Magento\Store\Model\StoreManagerInterface');
        $this->storeModel = $this->getMock('Magento\Store\Model\Store', ['__wakeup', 'getId'], [], '', false);
        $this->combineFactory = $this->getMock(
            'Magento\CatalogRule\Model\Rule\Condition\CombineFactory',
            [
                'create'
            ],
            [],
            '',
            false
        );
        $this->productModel = $this->getMock(
            'Magento\Catalog\Model\Product',
            [
                '__wakeup', 'getId', 'setData'
            ],
            [],
            '',
            false
        );
        $this->condition = $this->getMock(
            'Magento\Rule\Model\Condition\Combine',
            [
                'setRule',
                'validate'
            ],
            [],
            '',
            false
        );
        $this->websiteModel = $this->getMock(
            'Magento\Store\Model\Website',
            [
                '__wakeup',
                'getId',
                'getDefaultStore'
            ],
            [],
            '',
            false
        );
        $this->_ruleProductProcessor = $this->getMock(
            '\Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor',
            [],
            [],
            '',
            false
        );

        $this->_productCollectionFactory = $this->getMock(
            '\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );

        $this->_resourceIterator = $this->getMock(
            '\Magento\Framework\Model\ResourceModel\Iterator',
            ['walk'],
            [],
            '',
            false
        );

        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->prepareObjectManager([
            [
                'Magento\Framework\Api\ExtensionAttributesFactory',
                $this->getMock('Magento\Framework\Api\ExtensionAttributesFactory', [], [], '', false)
            ],
            [
                'Magento\Framework\Api\AttributeValueFactory',
                $this->getMock('Magento\Framework\Api\AttributeValueFactory', [], [], '', false)
            ],
        ]);

        $this->rule = $this->objectManagerHelper->getObject(
            'Magento\CatalogRule\Model\Rule',
            [
                'storeManager' => $this->storeManager,
                'combineFactory' => $this->combineFactory,
                'ruleProductProcessor' => $this->_ruleProductProcessor,
                'productCollectionFactory' => $this->_productCollectionFactory,
                'resourceIterator' => $this->_resourceIterator,
            ]
        );
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
        $indexer = $this->getMock('\Magento\Framework\Indexer\IndexerInterface');
        $indexer->expects($this->once())->method('invalidate');
        $this->_ruleProductProcessor->expects($this->once())->method('getIndexer')->will($this->returnValue($indexer));
        $this->rule->afterDelete();
    }

    /**
     * Test after update action
     *
     * @return void
     */
    public function testAfterUpdate()
    {
        $this->rule->isObjectNew(false);
        $indexer = $this->getMock('\Magento\Framework\Indexer\IndexerInterface');
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
        $indexer = $this->getMock('\Magento\Framework\Indexer\IndexerInterface');
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

    /**
     * @param $map
     */
    private function prepareObjectManager($map)
    {
        $objectManagerMock = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $objectManagerMock->expects($this->any())->method('getInstance')->willReturnSelf();
        $objectManagerMock->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap($map));
        $reflectionClass = new \ReflectionClass('Magento\Framework\App\ObjectManager');
        $reflectionProperty = $reflectionClass->getProperty('_instance');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($objectManagerMock);
    }
}
