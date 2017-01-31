<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Test\Unit\Model\ResourceModel\Rule;

use Magento\SalesRule\Model\ResourceModel\Rule\DateApplier;

/**
 * Class DateApplierTest
 */
class DateApplierTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Rule\DateApplier|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $model;
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * Setup the test
     */
    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->model = $this->objectManager->getObject(
            '\Magento\SalesRule\Model\ResourceModel\Rule\DateApplier',
            [
            ]
        );
    }

    /**
     * test ApplyDate
     */
    public function testApplyDate()
    {
        $className = '\Magento\Framework\DB\Select';
        /** @var \Magento\Framework\DB\Select|\PHPUnit_Framework_MockObject_MockObject $select */
        $select = $this->getMock($className, [], [], '', false);

        $select->expects($this->exactly(2))
            ->method('where')
            ->willReturnSelf();

        $now = date('Y-m-d');

        $this->model->applyDate($select, $now);
    }
}
