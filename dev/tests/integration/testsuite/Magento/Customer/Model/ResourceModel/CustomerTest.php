<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);
namespace Magento\Customer\Model\ResourceModel;

use Magento\Framework\ObjectManagerInterface;
use Magento\Customer\Model\Customer as CustomerModel;

/**
 * Integration test for @see \Magento\Customer\Model\ResourceModel\Customer
 */
class CustomerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CustomerModel
     */
    private $customerModel;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->customerModel = $this->objectManager->create(CustomerModel::class);
        parent::setUp();
    }

    /**
     * Test save rp token
     *
     * @throws \Exception
     */
    public function testSave(): void
    {
        $token='randomstring';
        $email= uniqid()."@example.com";

        $this->customerModel->setData(
            [
                'email' => $email,
                'rp_token' => $token,
                'firstname'=> 'John',
                'lastname' => 'Doe'
            ]
        )->save();

        $consumerResourceModel = $this->customerModel->getResource();

        $this->assertEquals($token, $this->customerModel->getRpToken());
        $this->assertNotEquals(
            $this->customerModel->getRpToken(),
            $consumerResourceModel->load($this->customerModel, 'rp_token')
        );
    }
}
