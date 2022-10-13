<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Controller\Index;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Message\MessageInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\AbstractController;
use Magento\TestFramework\Wishlist\Model\GetWishlistByCustomerId;
use Laminas\Stdlib\Parameters;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\TestFramework\Mail\Template\TransportBuilderMock;
use Magento\Wishlist\Model\DataSerializer;
use Magento\Framework\UrlInterface;

/**
 * Test for add product to wish list.
 *
 * @magentoDbIsolation disabled
 * @magentoAppArea frontend
 * @magentoDataFixture Magento/Customer/_files/customer.php
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddTest extends AbstractController
{
    /** @var Session */
    private $customerSession;

    /** @var GetWishlistByCustomerId */
    private $getWishlistByCustomerId;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var Escaper */
    private $escaper;

    /** @var CustomerRepositoryInterface */
    private $customerRepository;

    /**
     * @var TransportBuilderMock
     */
    private $transportBuilder;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var DataSerializer
     */
    private $dataSerializer;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->customerSession = $this->_objectManager->get(Session::class);
        $this->getWishlistByCustomerId = $this->_objectManager->get(GetWishlistByCustomerId::class);
        $this->productRepository = $this->_objectManager->get(ProductRepositoryInterface::class);
        $this->productRepository->cleanCache();
        $this->escaper = $this->_objectManager->get(Escaper::class);
        $this->customerRepository = $this->_objectManager->get(CustomerRepositoryInterface::class);
        $this->transportBuilder = $this->_objectManager->get(TransportBuilderMock::class);
        $this->urlBuilder = $this->_objectManager->get(UrlInterface::class);
        $this->dataSerializer = $this->_objectManager->get(DataSerializer::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->customerSession->setCustomerId(null);

        parent::tearDown();
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple_xss.php
     *
     * @return void
     */
    public function testAddActionProductNameXss(): void
    {
        $this->prepareReferer();
        $this->customerSession->setCustomerId(1);
        $product = $this->productRepository->get('product-with-xss');
        $escapedProductName = $this->escaper->escapeHtml($product->getName());
        $this->performAddToWishListRequest(['product' => $product->getId()]);
        $this->assertSuccess(1, 1, $escapedProductName);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Wishlist/_files/wishlist_with_configurable_product.php
     * @magentoDbIsolation disabled
     *
     * @return void
     */
    public function testAddConfigurableProductToWishList(): void
    {
        $this->prepareReferer();
        $this->customerSession->setCustomerId(1);
        $product = $this->productRepository->get('Configurable product');
        $this->performAddToWishListRequest(['product' => $product->getId()]);
        $this->assertSuccess(1, 1, $product->getName());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
     *
     * @return void
     */
    public function testAddDisabledProductToWishList(): void
    {
        $expectedMessage = $this->escaper->escapeHtml("We can't specify a product.");
        $this->customerSession->setCustomerId(1);
        $product = $this->productRepository->get('simple3');
        $this->performAddToWishListRequest(['product' => $product->getId()]);
        $this->assertSessionMessages($this->equalTo([(string)__($expectedMessage)]), MessageInterface::TYPE_ERROR);
        $this->assertRedirect($this->stringContains('wishlist/'));
    }

    /**
     * @return void
     */
    public function testAddToWishListWithoutParams(): void
    {
        $this->customerSession->setCustomerId(1);
        $this->performAddToWishListRequest([]);
        $this->assertSessionMessages($this->isEmpty(), MessageInterface::TYPE_ERROR);
        $this->assertSessionMessages($this->isEmpty(), MessageInterface::TYPE_SUCCESS);
        $this->assertRedirect($this->stringContains('wishlist/'));
    }

    /**
     * @return void
     */
    public function testAddNotExistingProductToWishList(): void
    {
        $this->customerSession->setCustomerId(1);
        $expectedMessage = $this->escaper->escapeHtml("We can't specify a product.");
        $this->performAddToWishListRequest(['product' => 989]);
        $this->assertSessionMessages($this->equalTo([(string)__($expectedMessage)]), MessageInterface::TYPE_ERROR);
        $this->assertRedirect($this->stringContains('wishlist/'));
    }

    /**
     * @return void
     */
    public function testAddToNotExistingWishList(): void
    {
        $expectedMessage = $this->escaper->escapeHtml("The requested Wish List doesn't exist.");
        $this->customerSession->setCustomerId(1);
        $this->performAddToWishListRequest(['wishlist_id' => 989]);
        $this->assertSessionMessages($this->equalTo([(string)__($expectedMessage)]), MessageInterface::TYPE_ERROR);
        $this->assert404NotFound();
    }

    /**
     * Add Product to Wishlist Before Login, Create Customer & Send Confirmation Email
     *
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoConfigFixture current_website customer/create_account/confirm 1
     *
     * @return void
     */
    public function testCreateCustomerWithEmailConfirmationAfterAddToWishlist(): void
    {
        $product = $this->productRepository->get('simple');
        $data = [];
        $data['product'] = (int)$product->getId();
        $this->customerSession->setBeforeWishlistRequest($data);
        $this->customerSession->setBeforeAuthUrl($this->urlBuilder->getUrl('wishlist/index/add'));
        $email = 'test_example_new@email.com';
        $this->fillRequestWithCustomerData($email);
        $this->dispatch('customer/account/createPost');
        $this->assertRedirect($this->stringContains('customer/account/index'));
        $message = 'You must confirm your account.'
            . ' Please check your email for the confirmation link or <a href="%1">click here</a> for a new link.';
        $url = $this->urlBuilder->getUrl('customer/account/confirmation', ['_query' => ['email' => $email]]);
        $this->assertSessionMessages($this->containsEqual((string)__($message, $url)), MessageInterface::TYPE_SUCCESS);
        /** @var CustomerInterface $customer */
        $customer = $this->customerRepository->get($email);
        $confirmation = $customer->getConfirmation();
        $sendMessage = $this->transportBuilder->getSentMessage();
        $this->assertNotNull($sendMessage);
        $rawMessage = $sendMessage->getBody()->getParts()[0]->getRawContent();
        $this->assertStringContainsString(
            (string)__(
                'You must confirm your %customer_email email before you can sign in (link is only valid once):',
                ['customer_email' => $email]
            ),
            $rawMessage
        );
        $this->assertStringContainsString(
            sprintf('token'),
            $rawMessage
        );
        $this->assertStringContainsString(
            sprintf('id=%s&amp;key=%s', $customer->getId(), $confirmation),
            $rawMessage
        );
    }

    /**
     * Save Wishlist Product Data into Cache.
     * Also Add Product To Wishlist based on the data retrieved from Cache Token
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture Magento/Customer/_files/customer_confirmation_config_enable.php
     * @magentoDataFixture Magento/Customer/_files/unconfirmed_customer.php
     * @magentoDbIsolation disabled
     *
     * @return void
     */
    public function testAddToWishlistOnCustomerConfirmation(): void
    {
        $this->prepareReferer();
        $product = $this->productRepository->get('simple');
        $data = [];
        $data['product'] = (int)$product->getId();
        $token = $this->dataSerializer->serialize($data);//Save into Cache
        $customer = $this->customerRepository->get('unconfirmedcustomer@example.com');
        $customer->setConfirmation(null);
        $this->customerRepository->save($customer);
        $this->assertEquals(null, $customer->getConfirmation());
        $this->customerSession->setCustomerId((int)$customer->getId());
        $this->getRequest()->setParams(['token' => $token])->setMethod(HttpRequest::METHOD_GET);
        $this->dispatch('wishlist/index/add');
        $this->assertSuccess((int)$customer->getId(), 1, $product->getName());
    }

    /**
     * @magentoConfigFixture current_store customer/captcha/enable 0
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testAddToWishlistBeforeLogin(): void
    {
        $this->prepareReferer();
        $product = $this->productRepository->get('simple');
        $this->performAddToWishListRequest(['product' => $product->getId()]);
        $this->assertSessionMessages(
            $this->equalTo([(string)__('You must login or register to add items to your wishlist.')]),
            MessageInterface::TYPE_ERROR
        );

        // re-initialize the application to make a second request
        Bootstrap::getInstance()->getBootstrap()->getApplication()->reinitialize();
        $this->_objectManager = Bootstrap::getObjectManager();
        $this->customerSession = $this->_objectManager->get(Session::class);
        $this->_request = null;
        $this->_response = null;

        // login
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue([
            'login' => [
                'username' => 'customer@example.com',
                'password' => 'password',
            ],
        ]);
        $this->dispatch('customer/account/loginPost');
        $this->assertTrue($this->customerSession->isLoggedIn());
        $this->assertSuccess(1, 1, $product->getName());
    }

    /**
     * Perform request add item to wish list.
     *
     * @param array $params
     * @return void
     */
    private function performAddToWishListRequest(array $params): void
    {
        $this->getRequest()->setParams($params)->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch('wishlist/index/add');
    }

    /**
     * Assert success response and items count.
     *
     * @param int $customerId
     * @param int $itemsCount
     * @param string $productName
     * @return void
     */
    private function assertSuccess(int $customerId, int $itemsCount, string $productName): void
    {
        $expectedMessage = sprintf("\n%s has been added to your Wish List.", $productName)
            . " Click <a href=\"http://localhost/test\">here</a> to continue shopping.";
        $this->assertSessionMessages($this->equalTo([(string)__($expectedMessage)]), MessageInterface::TYPE_SUCCESS);
        $wishlist = $this->getWishlistByCustomerId->execute($customerId);
        $this->assertCount($itemsCount, $wishlist->getItemCollection());
        $this->assertRedirect($this->stringContains('wishlist/index/index/wishlist_id/' . $wishlist->getId()));
    }

    /**
     * Prepare referer to test.
     *
     * @return void
     */
    private function prepareReferer(): void
    {
        $parameters = $this->_objectManager->create(Parameters::class);
        $parameters->set('HTTP_REFERER', 'http://localhost/test');
        $this->getRequest()->setServer($parameters);
    }

    /**
     * Fills request with customer data.
     *
     * @param string $email
     * @return void
     */
    private function fillRequestWithCustomerData(string $email): void
    {
        $this->getRequest()
            ->setMethod(HttpRequest::METHOD_POST)
            ->setParam(CustomerInterface::FIRSTNAME, 'firstname1')
            ->setParam(CustomerInterface::LASTNAME, 'lastname1')
            ->setParam(CustomerInterface::EMAIL, $email)
            ->setParam('password', '_Password1')
            ->setParam('password_confirmation', '_Password1');
    }
}
