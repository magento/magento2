<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Persistent\Helper;

use Magento\Framework\ObjectManagerInterface;
use Magento\Persistent\Model\SessionFactory;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test for persistent session helper
 *
 * @see \Magento\Persistent\Helper\Session
 * @magentoDbIsolation enabled
 */
class SessionTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var Session */
    private $helper;

    /** @var SessionFactory */
    private $sessionFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->helper = $this->objectManager->get(Session::class);
        $this->sessionFactory = $this->objectManager->get(SessionFactory::class);
    }

    /**
     * @magentoDataFixture Magento/Persistent/_files/persistent.php
     * @magentoConfigFixture current_store persistent/options/enabled 1
     *
     * @return void
     */
    public function testPersistentEnabled(): void
    {
        $session = $this->sessionFactory->create()->loadByCustomerId(1);
        $this->helper->setSession($session);
        $this->assertTrue($this->helper->isPersistent());
    }

    /**
     * @magentoDataFixture Magento/Persistent/_files/persistent.php
     * @magentoConfigFixture current_store persistent/options/enabled 0
     *
     * @return void
     */
    public function testPersistentDisabled(): void
    {
        $session = $this->sessionFactory->create()->loadByCustomerId(1);
        $this->helper->setSession($session);
        $this->assertFalse($this->helper->isPersistent());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoConfigFixture current_store persistent/options/enabled 1
     *
     * @return void
     */
    public function testCustomerWithoutPersistent(): void
    {
        $session = $this->sessionFactory->create()->loadByCustomerId(1);
        $this->helper->setSession($session);
        $this->assertFalse($this->helper->isPersistent());
    }
}
