<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Model;

class RuleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CatalogRule\Model\Rule
     */
    protected $_object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $resourceMock = $this->createPartialMock(
            \Magento\CatalogRule\Model\ResourceModel\Rule::class,
            ['getIdFieldName', 'getRulesFromProduct']
        );
        $resourceMock->expects($this->any())->method('getIdFieldName')->will($this->returnValue('id'));
        $resourceMock->expects(
            $this->any()
        )->method(
            'getRulesFromProduct'
        )->will(
            $this->returnValue($this->_getCatalogRulesFixtures())
        );

        $this->_object = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\CatalogRule\Model\Rule::class,
            ['resource' => $resourceMock]
        );
    }

    /**
     * @magentoAppIsolation enabled
     * @covers \Magento\CatalogRule\Model\Rule::calcProductPriceRule
     */
    public function testCalcProductPriceRule()
    {
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Product::class
        );
        $this->assertEquals($this->_object->calcProductPriceRule($product, 100), 45);
        $product->setParentId(true);
        $this->assertEquals($this->_object->calcProductPriceRule($product, 50), 50);
    }

    /**
     * Get array with catalog rule data
     *
     * @return array
     */
    protected function _getCatalogRulesFixtures()
    {
        return [
            [
                'action_operator' => 'by_percent',
                'action_amount' => '50.0000',
                'action_stop' => '0'
            ],
            [
                'action_operator' => 'by_percent',
                'action_amount' => '10.0000',
                'action_stop' => '0'
            ]
        ];
    }
}
