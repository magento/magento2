<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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