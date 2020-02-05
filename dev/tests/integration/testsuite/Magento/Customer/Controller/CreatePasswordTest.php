<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Controller;

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\ResourceModel\Customer as CustomerResource;
use Magento\Customer\Model\Session;
use Magento\Framework\Math\Random;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\AbstractController;

/**
 * Class checks password forgot scenarios
 *
 * @magentoDbIsolation enabled
 */
class CreatePasswordTest extends AbstractController
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var Session */
    private $session;

    /** @var LayoutInterface */
    private $layout;

    /** @var Random */
    private $random;

    /** @var CustomerFactory */
    private $customerFactory;

    /** @var Customer */
    private $customer;

    /** @var CustomerResource */
    private $customerResource;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->session = $this->objectManager->get(Session::class);
        $this->layout = $this->objectManager->get(LayoutInterface::class);
        $this->random = $this->objectManager->get(Random::class);
        $this->customerFactory = $this->objectManager->get(CustomerFactory::class);
        $defaultWebsite = $this->objectManager->get(WebsiteRepositoryInterface::class)->get('base')->getId();
        $this->customer = $this->customerFactory->create();
        $this->customer->setWebsiteId($defaultWebsite);
        $this->customerResource = $this->objectManager->get(CustomerResource::class);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer_with_website.php
     *
     * @return void
     */
    public function testCreatePassword()
    {
        $this->customer->loadByEmail('john.doe@magento.com');
        $token = $this->random->getUniqueHash();
        $this->customer->changeResetPasswordLinkToken($token);
        $this->customer->setData('confirmation', 'confirmation');
        $this->customerResource->save($this->customer);
        $this->session->setRpToken($token);
        $this->session->setRpCustomerId($this->customer->getId());
        $this->dispatch('customer/account/createPassword');
        $block = $this->layout->getBlock('resetPassword');
        $this->assertEquals($token, $block->getResetPasswordLinkToken());
    }
}
