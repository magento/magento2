<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Controller;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Quote\Model\GetQuoteByReservedOrderId;

class ValidateMinicartEmptyAfterClearingSessionTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var CartManagementInterface
     */
    private $cartManagement;

    /**
     * @var GetQuoteByReservedOrderId
     */
    private $getQuoteByReservedOrderId;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /** @var CheckoutSession */
    private $checkoutSession;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->filesystem = $this->objectManager->get(Filesystem::class);
        $this->getQuoteByReservedOrderId = $this->objectManager->get(GetQuoteByReservedOrderId::class);
        $this->customerRepository = $this->objectManager->get(CustomerRepositoryInterface::class);
        $this->cartManagement = $this->objectManager->get(CartManagementInterface::class);
        $this->checkoutSession = $this->objectManager->get(CheckoutSession::class);
    }

    /**
     * @magentoDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     * @return void
     * @throws FileSystemException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function testValidateEmptyMinicartAfterSessionClear(): void
    {
        $customer = $this->customerRepository->get('customer@example.com');
        $quote = $this->getQuoteByReservedOrderId->execute('test_order_with_simple_product_without_address');
        $result = $this->cartManagement->assignCustomer($quote->getId(), $customer->getId(), $customer->getStoreId());
        $this->assertTrue($result);
        $customerQuote = $this->cartManagement->getCartForCustomer($customer->getId());
        $this->checkoutSession->setQuoteId($customerQuote->getId());
        $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR)->delete(DirectoryList::SESSION);
        $this->dispatch('backend/admin/cache/flushAll');
        $this->assertEquals(0, $this->checkoutSession->getQuote()->getItemsCount());
    }
}
