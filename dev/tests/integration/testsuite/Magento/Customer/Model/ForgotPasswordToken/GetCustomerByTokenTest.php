<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\ForgotPasswordToken;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class GetCustomerByTokenTest extends TestCase
{
    private const RESET_PASSWORD = '8ed8677e6c79e68b94e61658bd756ea5';

    /** @var ObjectManagerInterface */
    private $objectManager;

    /**
     * @var GetCustomerByToken
     */
    private $customerByToken;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->customerByToken = $this->objectManager->get(GetCustomerByToken::class);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testExecuteWithNoSuchEntityException(): void
    {
        self::expectException(NoSuchEntityException::class);
        self::expectExceptionMessage('No such entity with rp_token = ' . self::RESET_PASSWORD);
        $this->customerByToken->execute(self::RESET_PASSWORD);
    }
}
