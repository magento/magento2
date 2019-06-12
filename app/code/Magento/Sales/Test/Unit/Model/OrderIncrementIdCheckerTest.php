<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD

namespace Magento\Sales\Test\Unit\Model;

=======
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model;

/**
 * Unit test for \Magento\Sales\Model\OrderIncrementIdChecker.
 */
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
class OrderIncrementIdCheckerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sales\Model\OrderIncrementIdChecker
     */
    private $model;

    /**
<<<<<<< HEAD
     * @var \Magento\Framework\App\ResourceConnection
=======
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    private $resourceMock;

    /**
<<<<<<< HEAD
     * @var \Magento\Framework\DB\Adapter\Pdo\Mysql
=======
     * @var \Magento\Framework\DB\Adapter\Pdo\Mysql|\PHPUnit_Framework_MockObject_MockObject
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    private $adapterMock;

    /**
<<<<<<< HEAD
     * @var \Magento\Framework\DB\Select
     */
    private $selectMock;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->selectMock = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->selectMock->expects($this->any())->method('from')->will($this->returnSelf());
        $this->selectMock->expects($this->any())->method('where');

        $this->adapterMock = $this->getMockBuilder(\Magento\Framework\DB\Adapter\Pdo\Mysql::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->adapterMock->expects($this->any())->method('select')->will($this->returnValue($this->selectMock));

        $this->resourceMock = $this->getMockBuilder(\Magento\Sales\Model\ResourceModel\Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceMock->expects(
            $this->any()
        )->method(
            'getConnection'
        )->will(
            $this->returnValue($this->adapterMock)
        );
=======
     * @var \Magento\Framework\DB\Select|\PHPUnit_Framework_MockObject_MockObject
     */
    private $selectMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->selectMock = $this->createMock(\Magento\Framework\DB\Select::class);
        $this->selectMock->expects($this->any())->method('from')->will($this->returnSelf());
        $this->selectMock->expects($this->any())->method('where');

        $this->adapterMock = $this->createMock(\Magento\Framework\DB\Adapter\Pdo\Mysql::class);
        $this->adapterMock->expects($this->any())->method('select')->will($this->returnValue($this->selectMock));

        $this->resourceMock = $this->createMock(\Magento\Sales\Model\ResourceModel\Order::class);
        $this->resourceMock->expects($this->any())->method('getConnection')->willReturn($this->adapterMock);
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc

        $this->model = $objectManagerHelper->getObject(
            \Magento\Sales\Model\OrderIncrementIdChecker::class,
            [
<<<<<<< HEAD
                'resourceModel' => $this->resourceMock
=======
                'resourceModel' => $this->resourceMock,
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
            ]
        );
    }

    /**
<<<<<<< HEAD
     * Unit test to verify if isOrderIncrementIdUsed method works with different types increment ids
     *
     * @param array $value
     * @dataProvider isOrderIncrementIdUsedDataProvider
     */
    public function testIsIncrementIdUsed($value)
=======
     * Unit test to verify if isOrderIncrementIdUsed method works with different types increment ids.
     *
     * @param string|int $value
     * @return void
     * @dataProvider isOrderIncrementIdUsedDataProvider
     */
    public function testIsIncrementIdUsed($value): void
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    {
        $expectedBind = [':increment_id' => $value];
        $this->adapterMock->expects($this->once())->method('fetchOne')->with($this->selectMock, $expectedBind);
        $this->model->isIncrementIdUsed($value);
    }

    /**
     * @return array
     */
<<<<<<< HEAD
    public function isOrderIncrementIdUsedDataProvider()
=======
    public function isOrderIncrementIdUsedDataProvider(): array
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    {
        return [[100000001], ['10000000001'], ['M10000000001']];
    }
}
