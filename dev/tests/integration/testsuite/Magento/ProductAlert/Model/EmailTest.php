<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ProductAlert\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Helper\View;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Mail\Template\TransportBuilderMock;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Test for Magento\ProductAlert\Model\Email class.
 *
 * @magentoAppIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EmailTest extends TestCase
{
    /**
     * @var Email
     */
    protected $_emailModel;

    /**
     * @var ObjectManager
     */
    protected $_objectManager;

    /**
     * @var AccountManagementInterface
     */
    protected $customerAccountManagement;

    /**
     * @var View
     */
    protected $_customerViewHelper;

    /**
     * @var TransportBuilderMock
     */
    private $transportBuilder;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->_objectManager = Bootstrap::getObjectManager();
        $this->customerAccountManagement = $this->_objectManager->create(
            AccountManagementInterface::class
        );
        $this->_customerViewHelper = $this->_objectManager->create(View::class);
        $this->transportBuilder = $this->_objectManager->get(TransportBuilderMock::class);
        $this->customerRepository = $this->_objectManager->create(CustomerRepositoryInterface::class);
        $this->productRepository = $this->_objectManager->create(ProductRepositoryInterface::class);

        $this->_emailModel = $this->_objectManager->create(Email::class);
    }

    /**
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @dataProvider customerFunctionDataProvider
     *
     * @param bool isCustomerIdUsed
     * @throws LocalizedException
     * @throws MailException
     * @throws NoSuchEntityException
     */
    public function testSend($isCustomerIdUsed)
    {
        /** @var Website $website */
        $website = $this->_objectManager->create(Website::class);
        $website->load(1);
        $this->_emailModel->setWebsite($website);

        $customer = $this->customerRepository->getById(1);

        if ($isCustomerIdUsed) {
            $this->_emailModel->setCustomerId(1);
        } else {
            $this->_emailModel->setCustomerData($customer);
        }

        /** @var Product $product */
        $product = $this->productRepository->getById(1);

        $this->_emailModel->addPriceProduct($product);
        $this->_emailModel->send();
        $emailMessage = quoted_printable_decode($this->transportBuilder->getSentMessage()->getBody()->bodyToString());
        $this->assertStringContainsString(
            'John Smith,',
            $emailMessage
        );
    }

    public static function customerFunctionDataProvider()
    {
        return [
            [true],
            [false],
        ];
    }

    /**
     * Assert that product price shown correct in email for customers with different customer groups.
     *
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Catalog/_files/product_simple_with_wholesale_tier_price.php
     * @magentoDataFixture Magento/Customer/_files/two_customers_with_different_customer_groups.php
     *
     * @return void
     * @throws LocalizedException
     * @throws MailException
     * @throws NoSuchEntityException
     */
    public function testEmailForDifferentCustomers(): void
    {
        $customerGeneral = $this->customerRepository->get('customer@example.com');
        $customerWholesale = $this->customerRepository->get('customer_two@example.com');
        $product = $this->productRepository->get('simple');

        /** @var Website $website */
        $website = $this->_objectManager->create(Website::class);
        $website->load(1);

        $data = [
            $customerGeneral->getId() => '10',
            $customerWholesale->getId() => '5',
        ];

        foreach ($data as $customerId => $expectedPrice) {
            $this->_emailModel->clean();
            $this->_emailModel->setCustomerId($customerId);
            $this->_emailModel->setWebsite($website);
            $this->_emailModel->addStockProduct($product);
            $this->_emailModel->setType('stock');
            $this->_emailModel->send();

            $expectedPriceBox = '<span id="product-price-' . $product->getId() . '" data-price-amount="'
                . $expectedPrice . '" data-price-type="finalPrice" '
                . 'class="price-wrapper "><span class="price">$' . $expectedPrice . '.00</span></span>';
            $emailMessage = quoted_printable_decode(
                $this->transportBuilder->getSentMessage()->getBody()->bodyToString()
            );
            $this->assertStringContainsString(
                $expectedPriceBox,
                $emailMessage
            );
        }
    }

    /**
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture Magento/Store/_files/second_store_with_second_identity.php
     */
    public function testScopedMessageIdentity()
    {
        /** @var Website $website */
        $website = $this->_objectManager->create(Website::class);
        $website->load(1);
        $this->_emailModel->setWebsite($website);

        /** @var StoreManagerInterface $storeManager */
        $storeManager = $this->_objectManager->create(StoreManagerInterface::class);
        $store = $storeManager->getStore('fixture_second_store');
        $this->_emailModel->setStoreId($store->getId());

        $customer = $this->customerRepository->getById(1);
        $this->_emailModel->setCustomerData($customer);

        /** @var Product $product */
        $product = $this->productRepository->getById(1);

        $this->_emailModel->addPriceProduct($product);
        $this->_emailModel->send();

        $from = $this->transportBuilder->getSentMessage()->getFrom()[0];
        $this->assertEquals('Fixture Store Owner', $from->getName());
        $this->assertEquals('fixture.store.owner@example.com', $from->getEmail());
    }
}
