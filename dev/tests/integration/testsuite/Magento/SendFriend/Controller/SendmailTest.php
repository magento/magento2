<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SendFriend\Controller;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Message\MessageInterface;
use Magento\TestFramework\Helper\Xpath;
use Magento\TestFramework\Mail\Template\TransportBuilderMock;
use Magento\TestFramework\Request;
use Magento\TestFramework\TestCase\AbstractController;

/**
 * Class checks send mail action
 *
 * @see \Magento\SendFriend\Controller\Product\Sendmail
 *
 * @magentoAppArea frontend
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class SendmailTest extends AbstractController
{
    private const MESSAGE_PRODUCT_LINK_XPATH = "//a[contains(@href, '%s') and contains(text(), '%s')]";

    /** @var Session */
    private $session;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var TransportBuilderMock */
    private $transportBuilder;

    /** @var array */
    private $staticData = [
        'sender' => [
            'name' => 'Test',
            'email' => 'test@example.com',
            'message' => 'Message',
        ],
        'recipients' => [
            'name' => [
                'Recipient 1',
                'Recipient 2'
            ],
            'email' => [
                'r1@example.com',
                'r2@example.com'
            ]
        ],
    ];

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->session = $this->_objectManager->get(Session::class);
        $this->productRepository = $this->_objectManager->get(ProductRepositoryInterface::class);
        $this->transportBuilder = $this->_objectManager->get(TransportBuilderMock::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->session->logout();
    }

    /**
     * Share the product to friend as logged in customer
     *
     * @magentoConfigFixture default_store sendfriend/email/allow_guest 0
     * @magentoConfigFixture default_store sendfriend/email/enabled 1
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Catalog/_files/products.php
     *
     * @return void
     */
    public function testSendActionAsLoggedIn(): void
    {
        $product = $this->productRepository->get('custom-design-simple-product');
        $this->session->loginById(1);
        $this->prepareRequestData();
        $this->dispatch('sendfriend/product/sendmail/id/' . $product->getId());
        $this->checkSuccess($product);
    }

    /**
     * Share the product to friend as guest customer
     *
     * @magentoConfigFixture default_store sendfriend/email/enabled 1
     * @magentoConfigFixture default_store sendfriend/email/allow_guest 1
     * @magentoDataFixture Magento/Catalog/_files/products.php
     *
     * @return void
     */
    public function testSendActionAsGuest(): void
    {
        $product = $this->productRepository->get('custom-design-simple-product');
        $this->prepareRequestData();
        $this->dispatch('sendfriend/product/sendmail/id/' . $product->getId());
        $this->checkSuccess($product);
    }

    /**
     * Share the product to friend as guest customer with invalid post data
     *
     * @magentoConfigFixture default_store sendfriend/email/enabled 1
     * @magentoConfigFixture default_store sendfriend/email/allow_guest 1
     * @magentoDataFixture Magento/Catalog/_files/products.php
     *
     * @return void
     */
    public function testSendActionAsGuestWithInvalidData(): void
    {
        $product = $this->productRepository->get('custom-design-simple-product');
        unset($this->staticData['sender']['email']);
        $this->prepareRequestData();
        $this->dispatch('sendfriend/product/sendmail/id/' . $product->getId());
        $this->assertSessionMessages(
            $this->equalTo([(string)__('Invalid Sender Email')]),
            MessageInterface::TYPE_ERROR
        );
    }

    /**
     * Share the product invisible in catalog to friend as guest customer
     *
     * @magentoConfigFixture default_store sendfriend/email/enabled 1
     * @magentoConfigFixture default_store sendfriend/email/allow_guest 1
     * @magentoDataFixture Magento/Catalog/_files/simple_products_not_visible_individually.php
     *
     * @return void
     */
    public function testSendInvisibleProduct(): void
    {
        $product = $this->productRepository->get('simple_not_visible_1');
        $this->prepareRequestData();
        $this->dispatch('sendfriend/product/sendmail/id/' . $product->getId());
        $this->assert404NotFound();
    }

    /**
     * Check success session message and email content
     *
     * @param ProductInterface $product
     * @return void
     */
    private function checkSuccess(ProductInterface $product): void
    {
        $this->assertSessionMessages(
            $this->equalTo([(string)__('The link to a friend was sent.')]),
            MessageInterface::TYPE_SUCCESS
        );
        $message = $this->transportBuilder->getSentMessage();
        $this->assertNotNull($message, 'The message was not sent');
        $content = $message->getBody()->getParts()[0]->getRawContent();
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(
                sprintf(self::MESSAGE_PRODUCT_LINK_XPATH, $product->getUrlKey(), $product->getName()),
                $content
            ),
            'Sent message does not contain product link'
        );
    }

    /**
     * Prepare request before dispatch
     *
     * @return void
     */
    private function prepareRequestData(): void
    {
        $this->getRequest()->setMethod(Request::METHOD_POST);
        $this->getRequest()->setPostValue($this->staticData);
    }
}
