<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

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
     * @var \Magento\Catalog\Model\Resource\Product\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_productCollectionFactory;

    /**
     * @var \Magento\Framework\Model\Resource\Iterator|\PHPUnit_Framework_MockObject_MockObject
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
            '\Magento\Catalog\Model\Resource\Product\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );

        $this->_resourceIterator = $this->getMock(
            '\Magento\Framework\Model\Resource\Iterator',
            ['walk'],
            [],
            '',
            false
        );

        $this->objectManagerHelper = new ObjectManagerHelper($this);
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
        $this->storeManager->expects($this->any())->method('getWebsites')->with(true)
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
     * Test after delete action
     *
     * @return void
     */
    public function testAfterDelete()
    {
        $indexer = $this->getMock('\Magento\Indexer\Model\IndexerInterface');
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
        $indexer = $this->getMock('\Magento\Indexer\Model\IndexerInterface');
        $indexer->expects($this->once())->method('invalidate');
        $this->_ruleProductProcessor->expects($this->once())->method('getIndexer')->will($this->returnValue($indexer));
        $this->rule->afterSave();
    }
}
