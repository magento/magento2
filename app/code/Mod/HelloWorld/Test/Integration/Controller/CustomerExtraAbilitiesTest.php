<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Mod\HelloWorld\Test\Integration\Controller;

use Magento\Backend\Model\Session;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Customer extra abilities attribute test.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerExtraAbilitiesTest extends AbstractBackendController
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->customerRepository = Bootstrap::getObjectManager()->get(
            CustomerRepositoryInterface::class
        );
    }

    /**
     * Test for the presence of an extra abilities attribute data in the customer edit form.
     *
     * @return void
     * @magentoDataFixture ../../../../app/code/Mod/HellowWorld/Test/_files/customer.php
     * @magentoAppArea adminhtml
     */
    public function testExtraAbilitiesViewAction(): void
    {
        $customer = $this->customerRepository->get('customer@example.com');
        $this->getRequest()->setMethod(HttpRequest::METHOD_GET);
        $this->getRequest()->setParam('id', $customer->getId());
        $this->dispatch('backend/customer/index/edit');
        $statusCode = $this->getResponse()->getStatusCode();
        $this->assertEquals(200, $statusCode);
        $formData = Bootstrap::getObjectManager()->get(Session::class)->getCustomerData();
        $isAllowAddDescription = $formData['account']['is_allowed_add_description'];
        $this->assertEquals(1, $isAllowAddDescription);
    }
}
