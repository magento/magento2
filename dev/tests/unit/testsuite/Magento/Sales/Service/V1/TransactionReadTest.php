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

namespace Magento\Sales\Service\V1;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class TransactionReadTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Sales\Service\V1\TransactionRead */
    protected $transactionRead;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Sales\Service\V1\Data\TransactionMapper|\PHPUnit_Framework_MockObject_MockObject */
    protected $transactionMapperMock;

    /** @var \Magento\Sales\Model\Order\Payment\TransactionRepository|\PHPUnit_Framework_MockObject_MockObject */
    protected $transactionRepositoryMock;

    /** @var \Magento\Sales\Service\V1\Data\TransactionSearchResultsBuilder|\PHPUnit_Framework_MockObject_MockObject */
    protected $searchResultsBuilderMock;

    protected function setUp()
    {
        $this->transactionMapperMock = $this->getMock(
            'Magento\Sales\Service\V1\Data\TransactionMapper',
            [],
            [],
            '',
            false
        );
        $this->transactionRepositoryMock = $this->getMock(
            'Magento\Sales\Model\Order\Payment\TransactionRepository',
            ['get', 'find'],
            [],
            '',
            false
        );
        $this->searchResultsBuilderMock = $this->getMock(
            'Magento\Sales\Service\V1\Data\TransactionSearchResultsBuilder',
            ['setItems', 'setTotalCount', 'setSearchCriteria', 'create'],
            [],
            '',
            false
        );

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->transactionRead = $this->objectManagerHelper->getObject(
            'Magento\Sales\Service\V1\TransactionRead',
            [
                'transactionMapper' => $this->transactionMapperMock,
                'transactionRepository' => $this->transactionRepositoryMock,
                'searchResultsBuilder' => $this->searchResultsBuilderMock
            ]
        );
    }

    public function testGet()
    {
        $id = 1;
        $transaction = $this->getMock('Magento\Sales\Model\Order\Payment\Transaction', [], [], '', false);
        $transactionDto = $this->getMock('Magento\Sales\Service\V1\Data\Transaction', [], [], '', false);
        $this->transactionRepositoryMock->expects($this->once())
            ->method('get')
            ->with($id)
            ->will($this->returnValue($transaction));
        $this->transactionMapperMock->expects($this->once())
            ->method('extractDto')
            ->with($transaction)
            ->will($this->returnValue($transactionDto));
        $this->assertEquals($transactionDto, $this->transactionRead->get($id));
    }

    public function testSearch()
    {
        /**
         * @var \Magento\Framework\Service\V1\Data\SearchCriteria $searchCriteria
         */
        $searchCriteria = $this->getMock('Magento\Framework\Service\V1\Data\SearchCriteria', [], [], '', false);
        $transactions = $this->getMock('Magento\Sales\Model\Order\Payment\Transaction', [], [], '', false);
        $transactionDto = $this->getMock('Magento\Sales\Service\V1\Data\Transaction', [], [], '', false);
        $searchResults = $this->getMock('Magento\Sales\Service\V1\Data\TransactionSearchResults', [], [], '', false);
        $this->transactionRepositoryMock->expects($this->once())
            ->method('find')
            ->with($searchCriteria)
            ->will($this->returnValue([$transactions]));
        $this->transactionMapperMock->expects($this->once())
            ->method('extractDto')
            ->with($transactions, true)
            ->will($this->returnValue($transactionDto));
        $this->searchResultsBuilderMock->expects($this->once())
            ->method('setItems')
            ->with([$transactionDto])
            ->willReturnSelf();
        $this->searchResultsBuilderMock->expects($this->once())
            ->method('setTotalCount')
            ->with(1)
            ->willReturnSelf();
        $this->searchResultsBuilderMock->expects($this->once())
            ->method('setSearchCriteria')
            ->with($searchCriteria)
            ->willReturnSelf();
        $this->searchResultsBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($searchResults);
        $this->assertEquals($searchResults, $this->transactionRead->search($searchCriteria));
    }
}
