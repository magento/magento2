<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Security\Model\ResourceModel\PasswordResetRequestEvent;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Security\Model\ResourceModel\PasswordResetRequestEvent\Collection
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
            ->create('Magento\Security\Model\ResourceModel\PasswordResetRequestEvent\Collection');
    }

    /**
     * filterByAccountReference() test
     *
     * @magentoDataFixture Magento/Security/_files/password_reset_request_events.php
     */
    public function testFilterByAccountReference()
    {
        $this->collectionModel->filterByAccountReference('test27.dev@gmail.com')
            ->load();

        $this->assertEquals(1, $this->collectionModel->getSize());
    }

    /**
     * filterByIp() test
     *
     * @magentoDataFixture Magento/Security/_files/password_reset_request_events.php
     */
    public function testFilterByIp()
    {
        $this->collectionModel->filterByIp('3232249856')
            ->load();

        $this->assertEquals(1, $this->collectionModel->getSize());
    }

    /**
     * filterByRequestType() test
     *
     * @magentoDataFixture Magento/Security/_files/password_reset_request_events.php
     */
    public function testFilterByRequestType()
    {
        $this->collectionModel
            ->filterByRequestType(\Magento\Security\Model\PasswordResetRequestEvent::ADMIN_PASSWORD_RESET_REQUEST)
            ->load();

        $this->assertGreaterThanOrEqual(3, $this->collectionModel->getSize());
    }

    /**
     * filterByLifetime() test
     *
     * @magentoDataFixture Magento/Security/_files/password_reset_request_events.php
     */
    public function testFilterByLifetime()
    {
        $startTime = strtotime('2016-01-19 15:42:13') - 10;
        $securityConfig = $this->objectManager
            ->create('\Magento\Security\Helper\SecurityConfig');
        $currentTime = $securityConfig->getCurrentTimestamp();
        $sessionLifetime = $currentTime - $startTime;

        $this->collectionModel->filterByLifetime($sessionLifetime)
            ->load();

        $this->assertGreaterThanOrEqual(1, $this->collectionModel->getSize());
    }

    /**
     * filterLastItem() test
     *
     * @magentoDataFixture Magento/Security/_files/password_reset_request_events.php
     */
    public function testFilterLastItem()
    {
        $this->collectionModel->filterLastItem()
            ->load();
        $this->assertEquals('2016-01-20 13:00:13', $this->collectionModel->getFirstItem()->getData('created_at'));
    }

    /**
     * filterByIpOrAccountReference() test
     *
     * @magentoDataFixture Magento/Security/_files/password_reset_request_events.php
     */
    public function testFilterByIpOrAccountReference()
    {
        $this->collectionModel->filterByIpOrAccountReference('3232249856', 'test273.dev@gmail.com')
            ->load();
        $this->assertEquals(2, $this->collectionModel->getSize());
    }

    /**
     * deleteRecordsOlderThen() test
     *
     * @magentoDataFixture Magento/Security/_files/password_reset_request_events.php
     */
    public function testDeleteRecordsOlderThen()
    {
        $startTime = strtotime('2016-01-20 13:00:13');
        $this->collectionModel->deleteRecordsOlderThen($startTime)
            ->load();
        $this->assertGreaterThanOrEqual(1, $this->collectionModel->getSize());
    }
}
