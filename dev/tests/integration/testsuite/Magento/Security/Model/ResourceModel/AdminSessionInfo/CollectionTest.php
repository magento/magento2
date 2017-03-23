<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Security\Model\ResourceModel\AdminSessionInfo;

use Magento\Framework\Stdlib\DateTime\DateTime;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Security\Model\ResourceModel\AdminSessionInfo\Collection
     */
    protected $collectionModel;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->collectionModel = $this->objectManager
            ->create(\Magento\Security\Model\ResourceModel\AdminSessionInfo\Collection::class);
    }

    /**
     * updateActiveSessionsStatus() test
     *
     * @magentoDataFixture Magento/Security/_files/adminsession.php
     */
    public function testUpdateActiveSessionsStatus()
    {
        $quantity = $this->collectionModel->updateActiveSessionsStatus(
            \Magento\Security\Model\AdminSessionInfo::LOGGED_OUT_BY_LOGIN,
            1,
            '569e2277752e9'
        );
        $this->assertGreaterThanOrEqual(1, $quantity);
    }

    /**
     * filterByUser() test
     *
     * @magentoDataFixture Magento/Security/_files/adminsession.php
     */
    public function testFilterByUser()
    {
        $this->collectionModel->filterByUser(
            1,
            \Magento\Security\Model\AdminSessionInfo::LOGGED_IN,
            '569e2e3d752e9'
        );
        $this->collectionModel->load();
        $this->assertGreaterThanOrEqual(1, $this->collectionModel->getSize());
    }

    /**
     * filterExpiredSessions() test
     *
     * @magentoDataFixture Magento/Security/_files/adminsession.php
     */
    public function testFilterExpiredSessions()
    {
        $startTime = strtotime('2016-01-19 15:42:13') - 1;
        $dateTime = $this->objectManager
            ->get(DateTime::class);
        $currentTime = $dateTime->gmtTimestamp();
        $sessionLifetime = $currentTime - $startTime;

        $this->collectionModel->filterExpiredSessions($sessionLifetime);
        $this->collectionModel->load();
        $this->assertGreaterThanOrEqual(1, $this->collectionModel->getSize());
    }

    /**
     * deleteSessionsOlderThen() test
     *
     * @magentoDataFixture Magento/Security/_files/adminsession.php
     */
    public function testDeleteSessionsOlderThen()
    {
        $startTime = strtotime('2016-01-19 15:42:13');
        $this->collectionModel->deleteSessionsOlderThen($startTime);
        $this->collectionModel->load();
        $this->assertGreaterThanOrEqual(1, $this->collectionModel->getSize());
    }
}
