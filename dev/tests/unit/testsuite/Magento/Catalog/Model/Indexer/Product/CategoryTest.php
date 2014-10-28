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

class CategoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Category\Product
     */
    protected $model;

    /**
     * @var \Magento\Catalog\Model\Indexer\Category\Product\Action\FullFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fullMock;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Category\Action\RowsFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $rowsMock;

    /**
     * @var \Magento\Indexer\Model\IndexerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $indexerMock;

    protected function setUp()
    {
        $this->fullMock = $this->getMock(
            'Magento\Catalog\Model\Indexer\Category\Product\Action\FullFactory',
            array('create'),
            array(),
            '',
            false
        );

        $this->rowsMock = $this->getMock(
            'Magento\Catalog\Model\Indexer\Product\Category\Action\RowsFactory',
            array('create'),
            array(),
            '',
            false
        );

        $this->indexerMock = $this->getMockForAbstractClass(
            'Magento\Indexer\Model\IndexerInterface',
            array(),
            '',
            false,
            false,
            true,
            array('getId', 'load', 'isInvalid', 'isWorking', '__wakeup')
        );

        $this->model = new \Magento\Catalog\Model\Indexer\Product\Category(
            $this->fullMock,
            $this->rowsMock,
            $this->indexerMock
        );
    }

    public function testExecuteWithIndexerWorking()
    {
        $ids = array(1, 2, 3);

        $this->indexerMock->expects(
            $this->once()
        )->method(
            'load'
        )->with(
            'catalog_product_category'
        )->will(
            $this->returnSelf()
        );
        $this->indexerMock->expects($this->once())->method('isWorking')->will($this->returnValue(true));

        $rowMock = $this->getMock(
            'Magento\Catalog\Model\Indexer\Product\Category\Action\Rows',
            array('execute'),
            array(),
            '',
            false
        );
        $rowMock->expects($this->at(0))->method('execute')->with($ids, true)->will($this->returnSelf());
        $rowMock->expects($this->at(1))->method('execute')->with($ids, false)->will($this->returnSelf());

        $this->rowsMock->expects($this->once())->method('create')->will($this->returnValue($rowMock));

        $this->model->execute($ids);
    }

    public function testExecuteWithIndexerNotWorking()
    {
        $ids = array(1, 2, 3);

        $this->indexerMock->expects(
            $this->once()
        )->method(
            'load'
        )->with(
            'catalog_product_category'
        )->will(
            $this->returnSelf()
        );
        $this->indexerMock->expects($this->once())->method('isWorking')->will($this->returnValue(false));

        $rowMock = $this->getMock(
            'Magento\Catalog\Model\Indexer\Product\Category\Action\Rows',
            array('execute'),
            array(),
            '',
            false
        );
        $rowMock->expects($this->once())->method('execute')->with($ids, false)->will($this->returnSelf());

        $this->rowsMock->expects($this->once())->method('create')->will($this->returnValue($rowMock));

        $this->model->execute($ids);
    }
}
