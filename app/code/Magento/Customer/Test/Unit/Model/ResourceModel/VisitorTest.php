<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\ResourceModel;

use Magento\Customer\Model\ResourceModel\Visitor;
use Magento\Customer\Model\Visitor as VisitorModel;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\DateTime as DateTimeDate;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Test for Visitor ResourceModel
 */
class VisitorTest extends TestCase
{
    /**
     * @var Visitor
     */
    private $model;

    /**
     * @var DateTimeDate|\PHPUnit\Framework\MockObject\MockObject
     */
    private $dateMock;

    /**
     * @var DateTime|\PHPUnit\Framework\MockObject\MockObject
     */
    private $dateTimeMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->dateMock = $this->createMock(DateTimeDate::class);
        $this->dateTimeMock = $this->createMock(DateTime::class);

        $contextMock = $this->createMock(\Magento\Framework\Model\ResourceModel\Db\Context::class);
        $connectionMock = $this->createMock(\Magento\Framework\DB\Adapter\AdapterInterface::class);
        $resourceMock = $this->createMock(\Magento\Framework\App\ResourceConnection::class);

        $contextMock->method('getResources')->willReturn($resourceMock);
        $resourceMock->method('getConnection')->willReturn($connectionMock);

        $this->model = $objectManager->getObject(
            Visitor::class,
            [
                'context' => $contextMock,
                'date' => $this->dateMock,
                'dateTime' => $this->dateTimeMock
            ]
        );
    }

    /**
     * Test that _prepareDataForSave includes website_id
     *
     * @return void
     */
    public function testPrepareDataForSaveIncludesWebsiteId(): void
    {
        $objectManager = new ObjectManager($this);
        $visitorMock = $objectManager->getObject(VisitorModel::class);
        $visitorMock->setCustomerId(123);
        $visitorMock->setWebsiteId(1);
        $visitorMock->setLastVisitAt('2024-01-01 00:00:00');

        $reflection = new ReflectionClass($this->model);
        $method = $reflection->getMethod('_prepareDataForSave');
        $method->setAccessible(true);

        $result = $method->invoke($this->model, $visitorMock);

        $this->assertArrayHasKey('customer_id', $result);
        $this->assertArrayHasKey('website_id', $result);
        $this->assertArrayHasKey('last_visit_at', $result);
        $this->assertEquals(123, $result['customer_id']);
        $this->assertEquals(1, $result['website_id']);
        $this->assertEquals('2024-01-01 00:00:00', $result['last_visit_at']);
    }

    /**
     * Test that _prepareDataForSave handles null website_id
     *
     * @return void
     */
    public function testPrepareDataForSaveWithNullWebsiteId(): void
    {
        $objectManager = new ObjectManager($this);
        $visitorMock = $objectManager->getObject(VisitorModel::class);
        $visitorMock->setCustomerId(null);
        $visitorMock->setWebsiteId(null);
        $visitorMock->setLastVisitAt('2024-01-01 00:00:00');

        $reflection = new ReflectionClass($this->model);
        $method = $reflection->getMethod('_prepareDataForSave');
        $method->setAccessible(true);

        $result = $method->invoke($this->model, $visitorMock);

        $this->assertArrayHasKey('website_id', $result);
        $this->assertNull($result['website_id']);
    }
}
