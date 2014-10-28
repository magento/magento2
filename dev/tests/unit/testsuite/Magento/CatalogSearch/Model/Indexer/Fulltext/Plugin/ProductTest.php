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

namespace Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin;

use Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin\Product;

class ProductTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Indexer\Model\IndexerInterface
     */
    protected $indexerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Product
     */
    protected $subjectMock;

    /**
     * @var Product
     */
    protected $model;

    protected function setUp()
    {
        $this->subjectMock = $this->getMock('Magento\Catalog\Model\Product', array(), array(), '', false);

        $this->indexerMock = $this->getMockForAbstractClass(
            'Magento\Indexer\Model\IndexerInterface',
            array(),
            '',
            false,
            false,
            true,
            array('getId', 'getState', '__wakeup')
        );

        $this->model = new Product($this->indexerMock);
    }

    public function testAfterSaveNonScheduled()
    {
        $this->indexerMock->expects($this->exactly(2))->method('getId')->will($this->returnValue(1));
        $this->indexerMock->expects($this->once())->method('isScheduled')->will($this->returnValue(false));
        $this->indexerMock->expects($this->once())->method('reindexRow')->with(1);

        $this->subjectMock->expects($this->once())->method('getId')->will($this->returnValue(1));

        $this->assertEquals($this->subjectMock, $this->model->afterSave($this->subjectMock));
    }

    public function testAfterSaveScheduled()
    {
        $this->indexerMock->expects($this->once())->method('getId')->will($this->returnValue(1));
        $this->indexerMock->expects($this->once())->method('isScheduled')->will($this->returnValue(true));
        $this->indexerMock->expects($this->never())->method('reindexRow');

        $this->subjectMock->expects($this->once())->method('getId')->will($this->returnValue(1));

        $this->assertEquals($this->subjectMock, $this->model->afterSave($this->subjectMock));
    }

    public function testAfterDeleteNonScheduled()
    {
        $this->indexerMock->expects($this->exactly(2))->method('getId')->will($this->returnValue(1));
        $this->indexerMock->expects($this->once())->method('isScheduled')->will($this->returnValue(false));
        $this->indexerMock->expects($this->once())->method('reindexRow')->with(1);

        $this->subjectMock->expects($this->once())->method('getId')->will($this->returnValue(1));

        $this->assertEquals($this->subjectMock, $this->model->afterDelete($this->subjectMock));
    }

    public function testAfterDeleteScheduled()
    {
        $this->indexerMock->expects($this->once())->method('getId')->will($this->returnValue(1));
        $this->indexerMock->expects($this->once())->method('isScheduled')->will($this->returnValue(true));
        $this->indexerMock->expects($this->never())->method('reindexRow');

        $this->subjectMock->expects($this->once())->method('getId')->will($this->returnValue(1));

        $this->assertEquals($this->subjectMock, $this->model->afterDelete($this->subjectMock));
    }
}
