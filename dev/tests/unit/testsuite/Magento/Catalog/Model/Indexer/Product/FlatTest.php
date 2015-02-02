<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product;

use Magento\TestFramework\Helper\ObjectManager;

class FlatTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat
     */
    private $model;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\Action\Row|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productFlatIndexerRow;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\Action\Rows|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productFlatIndexerRows;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\Action\Full|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productFlatIndexerFull;

    protected function setUp()
    {
        $this->productFlatIndexerRow = $this->getMockBuilder('Magento\Catalog\Model\Indexer\Product\Flat\Action\Row')
            ->disableOriginalConstructor()
            ->getMock();

        $this->productFlatIndexerRows = $this->getMockBuilder('Magento\Catalog\Model\Indexer\Product\Flat\Action\Rows')
            ->disableOriginalConstructor()
            ->getMock();

        $this->productFlatIndexerFull = $this->getMockBuilder('Magento\Catalog\Model\Indexer\Product\Flat\Action\Full')
            ->disableOriginalConstructor()
            ->getMock();

        $helper = new ObjectManager($this);
        $this->model = $helper->getObject(
            'Magento\Catalog\Model\Indexer\Product\Flat',
            [
                'productFlatIndexerRow' => $this->productFlatIndexerRow,
                'productFlatIndexerRows' => $this->productFlatIndexerRows,
                'productFlatIndexerFull' => $this->productFlatIndexerFull
            ]
        );
    }

    public function testExecuteAndExecuteList()
    {
        $except = ['test'];
        $this->productFlatIndexerRows->expects($this->any())->method('execute')->with($this->equalTo($except));

        $this->model->execute($except);
        $this->model->executeList($except);
    }

    public function testExecuteFull()
    {
        $this->productFlatIndexerFull->expects($this->any())->method('execute');

        $this->model->executeFull();
    }

    public function testExecuteRow()
    {
        $except = 5;
        $this->productFlatIndexerRow->expects($this->any())->method('execute')->with($this->equalTo($except));

        $this->model->executeRow($except);
    }
}
