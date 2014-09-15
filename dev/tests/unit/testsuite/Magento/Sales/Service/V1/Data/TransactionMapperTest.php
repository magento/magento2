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

namespace Magento\Sales\Service\V1\Data;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class TransactionMapperTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Sales\Service\V1\Data\TransactionMapper */
    protected $transactionMapper;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $transactionBuilderFactoryMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $transactionBuilderMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $additionalInformationBuilderMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $additionalInformationMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $transactionMapperFactoryMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $transactionMapperMock;


    protected function setUp()
    {
        $this->transactionBuilderFactoryMock = $this->getMock(
            'Magento\Sales\Service\V1\Data\TransactionBuilderFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->transactionBuilderMock = $this->getMock(
            'Magento\Sales\Service\V1\Data\TransactionBuilder',
            [
                'setTransactionId',
                'setParentId',
                'setOrderId',
                'setTxnId',
                'setPaymentId',
                'setParentTxnId',
                'setTxnType',
                'setIsClosed',
                'setCreatedAt',
                'setMethod',
                'setAdditionalInformation',
                'setIncrementId',
                'setChildTransactions',
                'create'
            ],
            [],
            '',
            false
        );
        $this->transactionBuilderFactoryMock->expects($this->any())->method('create')->will(
            $this->returnValue($this->transactionBuilderMock)
        );

        $this->additionalInformationBuilderMock = $this->getMock(
            'Magento\Sales\Service\V1\Data\Transaction\AdditionalInformationBuilder',
            ['create', 'populateWithArray'],
            [],
            '',
            false
        );
        $this->additionalInformationMock = $this->getMock(
            'Magento\Sales\Service\V1\Data\Transaction\AdditionalInformation',
            [],
            [],
            '',
            false
        );
        $this->additionalInformationBuilderMock->expects($this->any())->method('create')->will(
            $this->returnValue($this->additionalInformationMock)
        );

        $this->transactionMapperFactoryMock = $this->getMock(
            'Magento\Sales\Service\V1\Data\TransactionMapperFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->transactionMapperMock = $this->getMock(
            'Magento\Sales\Service\V1\Data\TransactionMapper',
            [],
            [],
            '',
            false
        );
        $this->transactionMapperFactoryMock->expects($this->any())->method('create')->will(
            $this->returnValue($this->transactionMapperMock)
        );

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->transactionMapper = $this->objectManagerHelper->getObject(
            'Magento\Sales\Service\V1\Data\TransactionMapper',
            [
                'transactionBuilderFactory' => $this->transactionBuilderFactoryMock,
                'additionalInfoBuilder' => $this->additionalInformationBuilderMock,
                'transactionMapperFactory' => $this->transactionMapperFactoryMock
            ]
        );
    }

    public function testGetAdditionalInfo()
    {
        $additionalInfo = ['keydata' => 'data'];
        $transactionModelMock = $this->getMockBuilder('Magento\Sales\Model\Order\Payment\Transaction')
            ->disableOriginalConstructor()->setMethods([])->getMock();

        $transactionModelMock->expects($this->once())->method('getAdditionalInformation')->will(
            $this->returnValue($additionalInfo)
        );
        $this->additionalInformationBuilderMock->expects($this->once())->method('populateWithArray')
            ->with(
                [
                    Transaction\AdditionalInformation::KEY => 'keydata',
                    Transaction\AdditionalInformation::VALUE => 'data'
                ]
            );

        $this->assertSame(
            [$this->additionalInformationMock],
            $this->transactionMapper->getAdditionalInfo($transactionModelMock)
        );

    }

    public function testGetIncrementId()
    {
        $id = 1;
        $transactionModelMock = $this->getMockBuilder('Magento\Sales\Model\Order\Payment\Transaction')
            ->disableOriginalConstructor()->setMethods([])->getMock();
        $this->prepareTransactionOrder($transactionModelMock, $id);

        $this->assertEquals($id, $this->transactionMapper->getIncrementId($transactionModelMock));
    }

    public function testGetChildTransactions()
    {
        $method = 'method';
        $transactionModelMock = $this->getMockBuilder('Magento\Sales\Model\Order\Payment\Transaction')
            ->disableOriginalConstructor()->setMethods(['getChildTransactions', 'getMethod', '__wakeup'])->getMock();
        $childModelMock = $this->getMockBuilder('Magento\Sales\Model\Order\Payment\Transaction')
            ->disableOriginalConstructor()->setMethods(['getChildTransactions', 'setMethod', '__wakeup'])->getMock();
        $transactionDtoMock = $this->getMockBuilder('Magento\Sales\Service\V1\Data\Transaction')
            ->disableOriginalConstructor()->setMethods([])->getMock();

        $transactionModelMock->expects($this->once())->method('getChildTransactions')->will(
            $this->returnValue([$childModelMock])
        );
        $transactionModelMock->expects($this->once())->method('getMethod')->will($this->returnValue($method));
        $childModelMock->expects($this->once())->method('setMethod')->with($method);
        $this->transactionMapperMock->expects($this->once())->method('extractDto')->with(
            $childModelMock,
            true
        )->will($this->returnValue($transactionDtoMock));

        $this->assertSame([$transactionDtoMock], $this->transactionMapper->getChildTransactions($transactionModelMock));
    }

    /**
     * @dataProvider lazyDataProvider
     * @param bool $lazy
     */
    public function testExtractDto($lazy)
    {
        $id = 1;
        $transactionModelMock = $this->getMockBuilder('Magento\Sales\Model\Order\Payment\Transaction')
            ->disableOriginalConstructor()->setMethods([])->getMock();
        $transactionDtoMock = $this->getMockBuilder('Magento\Sales\Service\V1\Data\Transaction')
            ->disableOriginalConstructor()->setMethods([])->getMock();
        $this->prepareTransactionOrder($transactionModelMock, $id);

        $this->transactionBuilderMock->expects($this->once())->method('setTransactionId');
        $this->transactionBuilderMock->expects($this->once())->method('setParentId');
        $this->transactionBuilderMock->expects($this->once())->method('setOrderId');
        $this->transactionBuilderMock->expects($this->once())->method('setTxnId');
        $this->transactionBuilderMock->expects($this->once())->method('setPaymentId');
        $this->transactionBuilderMock->expects($this->once())->method('setParentTxnId');
        $this->transactionBuilderMock->expects($this->once())->method('setTxnType');
        $this->transactionBuilderMock->expects($this->once())->method('setIsClosed');
        $this->transactionBuilderMock->expects($this->once())->method('setCreatedAt');
        $this->transactionBuilderMock->expects($this->once())->method('setMethod');
        $transactionModelMock->expects($this->once())->method('getAdditionalInformation')->will($this->returnValue([]));
        $this->transactionBuilderMock->expects($this->once())->method('setAdditionalInformation')->with([]);
        $this->transactionBuilderMock->expects($this->once())->method('setIncrementId')->with($id);
        $transactionModelMock->expects($this->any())->method('getChildTransactions')->will($this->returnValue([]));
        $this->transactionBuilderMock->expects($this->once())->method('setChildTransactions')->with([]);
        $this->transactionBuilderMock->expects($this->once())->method('create')->will(
            $this->returnValue($transactionDtoMock)
        );

        $this->assertSame($transactionDtoMock, $this->transactionMapper->extractDto($transactionModelMock, $lazy));
    }

    /**
     * @return array
     */
    public function lazyDataProvider()
    {
        return [
            [true],
            [false]
        ];
    }

    /**
     * Prepares transaction mock with mocked order
     *
     * @param $transactionModelMock
     * @param $id
     */
    private function prepareTransactionOrder($transactionModelMock, $id)
    {
        $orderMock = $this->getMockBuilder('Magento\Sales\Model\Order')
            ->disableOriginalConstructor()->setMethods(['getIncrementId', '__wakeup'])->getMock();

        $transactionModelMock->expects($this->once())->method('getOrder')->will(
            $this->returnValue($orderMock)
        );
        $orderMock->expects($this->once())->method('getIncrementId')->will($this->returnValue($id));
    }
}
