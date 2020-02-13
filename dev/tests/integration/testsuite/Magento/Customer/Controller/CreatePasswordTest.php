<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Controller;

use Magento\Customer\Model\CustomerRegistry;
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

    /** @var CustomerResource */
    private $customerResource;

    /** @var CustomerRegistry */
    private $customerRegistry;

    /** @var WebsiteRepositoryInterface */
    private $websiteRepository;

    /** @var int */
    private $customerId;

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
        $this->customerResource = $this->objectManager->get(CustomerResource::class);
        $this->customerRegistry = $this->objectManager->get(CustomerRegistry::class);
        $this->websiteRepository = $this->objectManager->get(WebsiteRepositoryInterface::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        $this->customerRegistry->remove($this->customerId);

        parent::tearDown();
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer_with_website.php
     *
     * @return void
     */
    public function testCreatePassword(): void
    {
        $defaultWebsite = $this->websiteRepository->get('base')->getId();
        $customer = $this->customerRegistry->retrieveByEmail('john.doe@magento.com', $defaultWebsite);
        $this->customerId = $customer->getId();
        $token = $this->random->getUniqueHash();
        $customer->changeResetPasswordLinkToken($token);
        $customer->setData('confirmation', 'confirmation');
        $this->customerResource->save($customer);
        $this->session->setRpToken($token);
        $this->session->setRpCustomerId($customer->getId());
        $this->dispatch('customer/account/createPassword');
        $block = $this->layout->getBlock('resetPassword');
        $this->assertEquals($token, $block->getResetPasswordLinkToken());
    }
}
