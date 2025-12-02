<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\NewRelicReporting\Test\Unit\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\NewRelicReporting\Model\ResourceModel\Users;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Unit test for Users ResourceModel
 *
 * @covers \Magento\NewRelicReporting\Model\ResourceModel\Users
 */
class UsersTest extends TestCase
{
    /**
     * @var Users
     */
    private Users $resourceModel;

    /**
     * @return void
     * @throws Exception
     */
    protected function setUp(): void
    {
        $context = $this->createMock(Context::class);
        $this->resourceModel = new Users($context);
    }

    /**
     * Test that the ResourceModel extends AbstractDb
     *
     * @return void
     */
    public function testExtendsAbstractDb(): void
    {
        $this->assertInstanceOf(AbstractDb::class, $this->resourceModel);
    }

    /**
     * Test that _construct initializes main table and primary key correctly
     *
     * @return void
     */
    public function testInitializesMainTableAndPrimaryKey(): void
    {
        $reflection = new ReflectionClass($this->resourceModel);

        $mainTableProperty = $reflection->getProperty('_mainTable');
        $mainTableProperty->setAccessible(true);
        $this->assertEquals('reporting_users', $mainTableProperty->getValue($this->resourceModel));

        $idFieldNameProperty = $reflection->getProperty('_idFieldName');
        $idFieldNameProperty->setAccessible(true);
        $this->assertEquals('entity_id', $idFieldNameProperty->getValue($this->resourceModel));
    }
}
