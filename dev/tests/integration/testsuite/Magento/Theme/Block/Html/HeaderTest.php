<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Block\Html;

use Magento\Customer\Model\Context;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test for Magento\Theme\Block\Html\Header
 *
 * @magentoDataFixture Magento/Customer/_files/customer.php
 * @magentoAppArea frontend
 */
class HeaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Theme\Block\Html\Header
     */
    protected $block;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $context;

    /**
     * Setup SUT
     */
    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->context = $objectManager->get('Magento\Framework\App\Http\Context');
        $this->context->setValue(Context::CONTEXT_AUTH, false, false);

        //Setup customer session
        $customerIdFromFixture = 1;
        $customerSession = Bootstrap::getObjectManager()->create('Magento\Customer\Model\Session');
        /** @var $customerService \Magento\Customer\Api\CustomerRepositoryInterface */
        $customerService = Bootstrap::getObjectManager()
            ->create('Magento\Customer\Api\CustomerRepositoryInterface');
        $customerData = $customerService->getById($customerIdFromFixture);
        $customerSession->setCustomerDataObject($customerData);

        //Create block and inject customer session
        $this->block = $objectManager->create(
            'Magento\Theme\Block\Html\Header',
            ['customerSession' => $customerSession]
        );
    }

    /**
     * Test default welcome message when customer is not logged in
     */
    public function testGetWelcomeDefault()
    {
        $this->assertEquals('Default welcome msg!', $this->block->getWelcome());
    }

    /**
     * Test welcome message when customer is logged in
     */
    public function testGetWelcomeLoggedIn()
    {
        $this->context->setValue(Context::CONTEXT_AUTH, true, false);
        $this->assertEquals('Welcome, John Smith!', $this->block->getWelcome());
    }
}
