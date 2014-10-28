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
namespace Magento\CatalogSearch\Model\Indexer;

class FulltextTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogSearch\Model\Indexer\Fulltext
     */
    protected $model;

    /**
     * @var \Magento\CatalogSearch\Model\Indexer\Fulltext\Action\FullFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fullMock;

    /**
     * @var \Magento\CatalogSearch\Model\Indexer\Fulltext\Action\RowsFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $rowsMock;

    /**
     * @var \Magento\Indexer\Model\IndexerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $indexerMock;

    protected function setUp()
    {
        $this->fullMock = $this->getMock(
            'Magento\CatalogSearch\Model\Indexer\Fulltext\Action\FullFactory',
            array('create'),
            array(),
            '',
            false
        );

        $this->rowsMock = $this->getMock(
            'Magento\CatalogSearch\Model\Indexer\Fulltext\Action\RowsFactory',
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

        $this->model = new \Magento\CatalogSearch\Model\Indexer\Fulltext(
            $this->fullMock,
            $this->rowsMock,
            $this->indexerMock
        );
    }

    public function testExecuteWithIndexer()
    {
        $ids = array(1, 2, 3);

        $this->indexerMock->expects(
            $this->once()
        )->method(
            'load'
        )->with(
            \Magento\CatalogSearch\Model\Indexer\Fulltext::INDEXER_ID
        )->will(
            $this->returnSelf()
        );

        $rowMock = $this->getMock(
            'Magento\CatalogSearch\Model\Indexer\Fulltext\Action\Rows',
            array('reindex'),
            array(),
            '',
            false
        );
        $rowMock->expects($this->once())->method('reindex')->with($ids)->will($this->returnSelf());

        $this->rowsMock->expects($this->once())->method('create')->will($this->returnValue($rowMock));

        $this->model->execute($ids);
    }
}
