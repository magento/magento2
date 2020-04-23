<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Model\ResourceModel\Rule;

use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\SalesRule\Model\ResourceModel\Rule\DateApplier;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DateApplierTest extends TestCase
{
    /**
     * @var DateApplier|MockObject
     */
    protected $model;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Setup the test
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->model = $this->objectManager->getObject(DateApplier::class, []);
    }

    /**
     * test ApplyDate
     */
    public function testApplyDate()
    {
        $className = Select::class;
        /** @var \Magento\Framework\DB\Select|MockObject $select */
        $select = $this->createMock($className);

        $select->expects($this->exactly(2))
            ->method('where')
            ->willReturnSelf();

        $now = date('Y-m-d');

        $this->model->applyDate($select, $now);
    }
}
