<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\OrderIncrementIdChecker;
use Magento\Sales\Model\ResourceModel\Order;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for \Magento\Sales\Model\OrderIncrementIdChecker.
 */
class OrderIncrementIdCheckerTest extends TestCase
{
    /**
     * @var OrderIncrementIdChecker
     */
    private $model;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resourceMock;

    /**
     * @var Mysql|MockObject
     */
    private $adapterMock;

    /**
     * @var Select|MockObject
     */
    private $selectMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);

        $this->selectMock = $this->createMock(Select::class);
        $this->selectMock->expects($this->any())->method('from')->willReturnSelf();
        $this->selectMock->expects($this->any())->method('where');

        $this->adapterMock = $this->createMock(Mysql::class);
        $this->adapterMock->expects($this->any())->method('select')->willReturn($this->selectMock);

        $this->resourceMock = $this->createMock(Order::class);
        $this->resourceMock->expects($this->any())->method('getConnection')->willReturn($this->adapterMock);

        $this->model = $objectManagerHelper->getObject(
            OrderIncrementIdChecker::class,
            [
                'resourceModel' => $this->resourceMock,
            ]
        );
    }

    /**
     * Unit test to verify if isOrderIncrementIdUsed method works with different types increment ids.
     *
     * @param string|int $value
     * @return void
     * @dataProvider isOrderIncrementIdUsedDataProvider
     */
    public function testIsIncrementIdUsed($value): void
    {
        $expectedBind = [':increment_id' => $value];
        $this->adapterMock->expects($this->once())->method('fetchOne')->with($this->selectMock, $expectedBind);
        $this->model->isIncrementIdUsed($value);
    }

    /**
     * @return array
     */
    public static function isOrderIncrementIdUsedDataProvider(): array
    {
        return [[100000001], ['10000000001'], ['M10000000001']];
    }
}
