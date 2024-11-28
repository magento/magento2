<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime;
use Magento\SalesRule\Model\ResourceModel\Coupon;
use Magento\SalesRule\Model\Rule;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CouponTest extends TestCase
{
    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var DateTime\DateTime|MockObject
     */
    private $dateMock;

    /**
     * @var DateTime|MockObject
     */
    private $datetimeMock;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resourcesMock;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connectionMock;
    /**
     * @var Coupon
     */
    private $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->contextMock = $this->createMock(Context::class);
        $this->dateMock = $this->createMock(DateTime\DateTime::class);
        $this->datetimeMock = $this->createMock(DateTime::class);
        $this->resourcesMock = $this->createMock(ResourceConnection::class);
        $this->connectionMock = $this->createMock(AdapterInterface::class);
        $this->contextMock->method('getResources')
            ->willReturn($this->resourcesMock);
        $this->resourcesMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connectionMock);
        $this->model = new Coupon(
            $this->contextMock,
            $this->dateMock,
            $this->datetimeMock
        );
    }

    /**
     * @dataProvider updateSpecificCouponsDataProvider
     * @param array $origData
     * @param array $data
     * @param array|null $update
     * @return void
     * @throws Exception
     */
    public function testUpdateSpecificCoupons(array $origData, array $data, ?array $update = null): void
    {
        /** @var Rule|MockObject $abstractModel */
        $ruleMock = $this->getMockBuilder(Rule::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['save'])
            ->getMock();

        $ruleMock->setData($origData);
        $ruleMock->setOrigData();
        $ruleMock->addData($data);

        $this->resourcesMock->expects($this->any())
            ->method('getTableName')
            ->willReturnArgument(0);

        if ($update) {
            $this->connectionMock->expects($this->once())
                ->method('update')
                ->with(
                    'salesrule_coupon',
                    $update,
                    ['rule_id = ?' => $data['id']]
                );
        } else {
            $this->connectionMock->expects($this->never())
                ->method('update');
        }
        $this->model->updateSpecificCoupons($ruleMock);
    }

    /**
     * @return array
     */
    public static function updateSpecificCouponsDataProvider(): array
    {
        return [
            [
                ['uses_per_coupon' => 1],
                ['uses_per_coupon' => 0],
                null
            ],
            [
                ['uses_per_customer' => 1],
                ['uses_per_customer' => 0],
                null
            ],
            [
                ['coupon_type' => Rule::COUPON_TYPE_SPECIFIC, 'uses_per_coupon' => 1, 'uses_per_customer' => 1],
                ['coupon_type' => Rule::COUPON_TYPE_AUTO, 'uses_per_coupon' => 0, 'uses_per_customer' => 0],
                null
            ],
            [
                ['uses_per_coupon' => 1],
                ['id' => 1, 'uses_per_coupon' => 0],
                ['usage_limit' => 0],
            ],
            [
                ['uses_per_customer' => 1],
                ['id' => 1, 'uses_per_customer' => 0],
                ['usage_per_customer' => 0],
            ],
            [
                ['coupon_type' => Rule::COUPON_TYPE_SPECIFIC, 'uses_per_coupon' => 1, 'uses_per_customer' => 1],
                ['id' => 1, 'coupon_type' => Rule::COUPON_TYPE_AUTO, 'uses_per_coupon' => 1, 'uses_per_customer' => 1],
                ['usage_limit' => 1, 'usage_per_customer' => 1],
            ],
            [
                ['uses_per_coupon' => 1, 'uses_per_customer' => 1],
                ['id' => 1, 'uses_per_coupon' => 0, 'uses_per_customer' => 0],
                ['usage_limit' => 0, 'usage_per_customer' => 0],
            ],
            [
                ['uses_per_coupon' => 1, 'uses_per_customer' => 1],
                ['id' => 1, 'uses_per_coupon' => 0, 'uses_per_customer' => 1],
                ['usage_limit' => 0],
            ],
            [
                ['uses_per_coupon' => 1, 'uses_per_customer' => 1],
                ['id' => 1, 'uses_per_coupon' => 1, 'uses_per_customer' => 0],
                ['usage_per_customer' => 0],
            ],
        ];
    }
}
