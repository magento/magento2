<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerAdminUi\Ui\Customer\Component\Control;

use Magento\Backend\Model\Search\AuthorizationMock;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Authorization;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Testing Login as Customer button with inactive customer
 *
 * @magentoAppArea adminhtml
 */
class LoginAsCustomerButtonTestWithInactiveCustomer extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @var LoginAsCustomerButton
     */
    private $loginAsCustomerButton;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->objectManager->addSharedInstance(
            $this->objectManager->get(AuthorizationMock::class),
            Authorization::class
        );
        $resource = $this->objectManager->get(ResourceConnection::class);
        $this->connection = $resource->getConnection();
        $this->loginAsCustomerButton = $this->objectManager->create(LoginAsCustomerButton::class);
        $this->registry = $this->objectManager->get(Registry::class);
    }

    /**
     * Checks if login as customer button is not available for admin if customer inactive
     *
     * @magentoDataFixture Magento/LoginAsCustomer/_files/customer.php
     * @magentoConfigFixture login_as_customer/general/enabled 1
     * @magentoDbIsolation disabled
     */
    public function testLoginAsCustomerButtonWithInactiveCustomer(): void
    {
        $this->registry->unregister(RegistryConstants::CURRENT_CUSTOMER_ID);
        $this->registry->register(RegistryConstants::CURRENT_CUSTOMER_ID, 1);
        $this->changeCustomerActive(false);
        $this->enableCustomerAssistanceAllowed();
        $data = $this->loginAsCustomerButton->getButtonData();
        $this->assertNotEmpty($data);
        $this->assertEquals(__('Login as Customer'), $data['label']);
        $this->assertStringContainsString('window.lacNotAllowedPopup()', $data['on_click']);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->registry->unregister(RegistryConstants::CURRENT_CUSTOMER_ID);
        $this->objectManager->removeSharedInstance(LoginAsCustomerButton::class);
    }

    /**
     * Make Customer inactive
     *
     * @param bool $active
     *
     * @return void
     */
    private function changeCustomerActive(bool $active): void
    {
        $this->connection->update(
            $this->connection->getTableName('customer_entity'),
            ['is_active' => $active],
            $this->connection->quoteInto('entity_id = ?', 1)
        );
    }

    /**
     * Enable customer assistance
     *
     * @return void
     */
    private function enableCustomerAssistanceAllowed(): void
    {
        $this->connection->insertOnDuplicate(
            $this->connection->getTableName('login_as_customer_assistance_allowed'),
            ['customer_id' => 1],
            ['customer_id']
        );
    }
}
