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
namespace Magento\Sales\Service\V1\Action;

/**
 * Class InvoiceListTest
 */
class InvoiceListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Service\V1\Action\InvoiceList
     */
    protected $invoiceList;

    /**
     * @var \Magento\Sales\Model\Order\InvoiceRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $invoiceRepositoryMock;

    /**
     * @var \Magento\Sales\Service\V1\Data\InvoiceMapper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $invoiceMapperMock;

    /**
     * @var \Magento\Sales\Service\V1\Data\InvoiceSearchResultsBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchResultsBuilderMock;

    /**
     * @var \Magento\Framework\Service\V1\Data\SearchCriteria|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchCriteriaMock;

    /**
     * @var \Magento\Sales\Model\Order\Invoice|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $invoiceMock;

    /**
     * @var \Magento\Sales\Service\V1\Data\Invoice|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataObjectMock;

    /**
     * SetUp
     */
    protected function setUp()
    {
        $this->invoiceRepositoryMock = $this->getMock(
            'Magento\Sales\Model\Order\InvoiceRepository',
            ['find'],
            [],
            '',
            false
        );
        $this->invoiceMapperMock = $this->getMock(
            'Magento\Sales\Service\V1\Data\InvoiceMapper',
            [],
            [],
            '',
            false
        );
        $this->searchResultsBuilderMock = $this->getMock(
            'Magento\Sales\Service\V1\Data\InvoiceSearchResultsBuilder',
            ['setItems', 'setSearchCriteria', 'create', 'setTotalCount'],
            [],
            '',
            false
        );
        $this->searchCriteriaMock = $this->getMock(
            'Magento\Framework\Service\V1\Data\SearchCriteria',
            [],
            [],
            '',
            false
        );
        $this->invoiceMock = $this->getMock(
            'Magento\Sales\Model\Order\Invoice',
            [],
            [],
            '',
            false
        );
        $this->dataObjectMock = $this->getMock(
            'Magento\Sales\Service\V1\Data\Invoice',
            [],
            [],
            '',
            false
        );
        $this->invoiceList = new InvoiceList(
            $this->invoiceRepositoryMock,
            $this->invoiceMapperMock,
            $this->searchResultsBuilderMock
        );
    }

    /**
     * test invoice list service
     */
    public function testInvoke()
    {
        $this->invoiceRepositoryMock->expects($this->once())
            ->method('find')
            ->with($this->equalTo($this->searchCriteriaMock))
            ->will($this->returnValue([$this->invoiceMock]));
        $this->invoiceMapperMock->expects($this->once())
            ->method('extractDto')
            ->with($this->equalTo($this->invoiceMock))
            ->will($this->returnValue($this->dataObjectMock));
        $this->searchResultsBuilderMock->expects($this->once())
            ->method('setItems')
            ->with($this->equalTo([$this->dataObjectMock]))
            ->will($this->returnSelf());
        $this->searchResultsBuilderMock->expects($this->once())
            ->method('setTotalCount')
            ->with($this->equalTo(count($this->invoiceMock)))
            ->will($this->returnSelf());
        $this->searchResultsBuilderMock->expects($this->once())
            ->method('setSearchCriteria')
            ->with($this->equalTo($this->searchCriteriaMock))
            ->will($this->returnSelf());
        $this->searchResultsBuilderMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue('expected-result'));
        $this->assertEquals('expected-result', $this->invoiceList->invoke($this->searchCriteriaMock));
    }
}
