<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\ForgotPasswordToken;

use Magento\Customer\Model\Customer;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Customer\Model\ForgotPasswordToken\ConfirmCustomerByToken.
 */
class ConfirmCustomerByTokenTest extends TestCase
{
    private const STUB_CUSTOMER_RESET_TOKEN = 'token12345';

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ConfirmCustomerByToken
     */
    private $confirmCustomerByToken;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();

        $resource = $this->objectManager->get(ResourceConnection::class);
        $this->connection = $resource->getConnection();

        $this->confirmCustomerByToken = $this->objectManager->get(ConfirmCustomerByToken::class);
    }

    /**
     * Customer address shouldn't validate during confirm customer by token
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     *
     * @return void
     */
    public function testExecuteWithInvalidAddress(): void
    {
        $id = 1;

        $customerModel = $this->objectManager->create(Customer::class);
        $customerModel->load($id);
        $customerModel->setRpToken(self::STUB_CUSTOMER_RESET_TOKEN);
        $customerModel->setRpTokenCreatedAt(date('Y-m-d H:i:s'));
        $customerModel->setConfirmation($customerModel->getRandomConfirmationKey());
        $customerModel->save();

        //make city address invalid
        $this->makeCityInvalid($id);

        $this->confirmCustomerByToken->resetCustomerConfirmation($id);
        $this->assertNull($customerModel->load($id)->getConfirmation());
    }

    /**
     * Set city invalid for customer address
     *
     * @param int $id
     * @return void
     */
    private function makeCityInvalid(int $id): void
    {
        $this->connection->update(
            $this->connection->getTableName('customer_address_entity'),
            ['city' => ''],
            $this->connection->quoteInto('entity_id = ?', $id)
        );
    }
}
