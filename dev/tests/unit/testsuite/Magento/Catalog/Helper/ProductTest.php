<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Helper;

class ProductTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Helper\Product
     */
    protected $_productHelper;

    protected function setUp()
    {
        $arguments = [
            'reindexPriceIndexerData' => [
                'byDataResult' => ['attribute'],
                'byDataChange' => ['attribute'],
            ],
        ];

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_productHelper = $objectManager->getObject('Magento\Catalog\Helper\Product', $arguments);
    }

    /**
     * @param mixed $data
     * @param boolean $result
     * @dataProvider getData
     */
    public function testIsDataForPriceIndexerWasChanged($data, $result)
    {
        $this->assertEquals($this->_productHelper->isDataForPriceIndexerWasChanged($data), $result);
    }

    /**
     * Data provider for testIsDataForPriceIndexerWasChanged
     * @return array
     */
    public function getData()
    {
        $product1 = $this->getMockBuilder('Magento\Catalog\Model\Product')->disableOriginalConstructor()->getMock();

        $product2 = $this->getMockBuilder('Magento\Catalog\Model\Product')->disableOriginalConstructor()->getMock();
        $product2->expects(
            $this->once()
        )->method(
            'getData'
        )->with(
            $this->equalTo('attribute')
        )->will(
            $this->returnValue(true)
        );

        $product3 = $this->getMockBuilder('Magento\Catalog\Model\Product')->disableOriginalConstructor()->getMock();
        $product3->expects(
            $this->once()
        )->method(
            'dataHasChangedFor'
        )->with(
            $this->equalTo('attribute')
        )->will(
            $this->returnValue(true)
        );

        return [
            [$product1, false],
            [$product2, true],
            [$product3, true],
            [['attribute' => ''], true],
            [['param' => ''], false],
            ['test', false]
        ];
    }
}
