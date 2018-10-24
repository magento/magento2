<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\TestStep;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\TestStep\CreateCustomerStep;
use Magento\Customer\Test\TestStep\DeleteCustomerStep;
use Magento\Mtf\TestStep\TestStepFactory;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Customized create customer step with remove cleanup.
 */
class SignifydCreateCustomerStep implements TestStepInterface
{
    /**
     * Customer fixture.
     *
     * @var Customer
     */
    private $customer;

    /**
     * Test step factory.
     *
     * @var TestStepFactory
     */
    private $testStepFactory;

    /**
     * @param Customer $customer
     * @param TestStepFactory $testStepFactory
     */
    public function __construct(
        Customer $customer,
        TestStepFactory $testStepFactory
    ) {
        $this->customer = $customer;
        $this->testStepFactory = $testStepFactory;
    }

    /**
     * Run step flow.
     *
     * @return void
     */
    public function run()
    {
        $this->getStepInstance(CreateCustomerStep::class)->run();
    }

    /**
     * @return void
     */
    public function cleanup()
    {
        $this->getStepInstance(CreateCustomerStep::class)->cleanup();
        $this->getStepInstance(DeleteCustomerStep::class)->run();
    }

    /**
     * Creates test step instance with preset params.
     *
     * @param string $class
     * @return TestStepInterface
     */
    private function getStepInstance($class)
    {
        return $this->testStepFactory->create(
            $class,
            ['customer' => $this->customer]
        );
    }
}
