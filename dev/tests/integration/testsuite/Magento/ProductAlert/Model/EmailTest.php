<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ProductAlert\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\ProductAlert\Model\Email;
use Magento\Store\Model\Website;
use Magento\TestFramework\Mail\Template\TransportBuilderMock;

/**
 * Test for Magento\ProductAlert\Model\Email class.
 *
 * @magentoAppIsolation enabled
 */
class EmailTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Email
     */
    protected $_emailModel;

    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Customer\Api\AccountManagementInterface
     */
    protected $customerAccountManagement;

    /**
     * @var \Magento\Customer\Helper\View
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
    protected function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->customerAccountManagement = $this->_objectManager->create(
            \Magento\Customer\Api\AccountManagementInterface::class
        );
        $this->_customerViewHelper = $this->_objectManager->create(\Magento\Customer\Helper\View::class);
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

        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->getById(1);

        $this->_emailModel->addPriceProduct($product);
        $this->_emailModel->send();

        $this->assertContains(
            'John Smith,',
            $this->transportBuilder->getSentMessage()->getBody()->getParts()[0]->getRawContent()
        );
    }

    public function customerFunctionDataProvider()
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

            $this->assertContains(
                $expectedPriceBox,
                $this->transportBuilder->getSentMessage()->getBody()->getParts()[0]->getRawContent()
            );
        }
    }
}
