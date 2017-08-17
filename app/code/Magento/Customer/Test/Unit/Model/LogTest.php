<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model;

/**
 * Customer log model test.
 */
class LogTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Customer log model.
     *
     * @var \Magento\Customer\Model\Log
     */
    protected $log;

    /**
     * @var array
     */
    protected $logData = [
        'customer_id' => 369,
        'last_login_at' => '2015-03-04 12:00:00',
        'last_visit_at' => '2015-03-04 12:01:00',
        'last_logout_at' => '2015-03-04 12:05:00',
    ];

    /**
     * @return void
     */
    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->log = $objectManagerHelper->getObject(
            \Magento\Customer\Model\Log::class,
            [
                'customerId' => $this->logData['customer_id'],
                'lastLoginAt' => $this->logData['last_login_at'],
                'lastVisitAt' => $this->logData['last_visit_at'],
                'lastLogoutAt' => $this->logData['last_logout_at']
            ]
        );
    }

    /**
     * @return void
     */
    public function testGetCustomerId()
    {
        $this->assertEquals($this->logData['customer_id'], $this->log->getCustomerId());
    }

    /**
     * @return void
     */
    public function testGetLastLoginAt()
    {
        $this->assertEquals($this->logData['last_login_at'], $this->log->getLastLoginAt());
    }

    /**
     * @return void
     */
    public function testGetLastVisitAt()
    {
        $this->assertEquals($this->logData['last_visit_at'], $this->log->getLastVisitAt());
    }

    /**
     * @return void
     */
    public function testGetLastLogoutAt()
    {
        $this->assertEquals($this->logData['last_logout_at'], $this->log->getLastLogoutAt());
    }
}
