<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Wishlist\Controller\Index;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\CustomerNameGenerationInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Message\MessageInterface;
use Magento\TestFramework\Helper\Xpath;
use Magento\TestFramework\Mail\Template\TransportBuilderMock;
use Magento\TestFramework\TestCase\AbstractController;

/**
 * Test sending wish list.
 *
 * @magentoDbIsolation disabled
 * @magentoAppArea frontend
 * @magentoDataFixture Magento/Wishlist/_files/wishlist.php
 */
class SendTest extends AbstractController
{
    /** @var Session */
    private $customerSession;

    /** @var CustomerNameGenerationInterface */
    private $customerNameGeneration;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var TransportBuilderMock */
    private $transportBuilder;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->customerSession = $this->_objectManager->get(Session::class);
        $this->customerNameGeneration = $this->_objectManager->get(CustomerNameGenerationInterface::class);
        $this->productRepository = $this->_objectManager->get(ProductRepositoryInterface::class);
        $this->productRepository->cleanCache();
        $this->transportBuilder = $this->_objectManager->get(TransportBuilderMock::class);
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
     * @return void
     */
    public function testSendWishList(): void
    {
        $product = $this->productRepository->get('simple');
        $this->customerSession->setCustomerId(1);
        $shareMessage = 'Here\'s what I want for my birthday.';
        $postValues = ['emails' => 'test@example.com', 'message' => $shareMessage];
        $this->dispatchSendWishListRequest($postValues);
        $this->assertSessionMessages(
            $this->equalTo([(string)__('Your wish list has been shared.')]),
            MessageInterface::TYPE_SUCCESS
        );
        $this->assertNotNull($this->transportBuilder->getSentMessage());
        $messageContent = quoted_printable_decode($this->transportBuilder->getSentMessage()->getBody()->bodyToString());
        $this->assertStringContainsString($shareMessage, $messageContent);
        $this->assertStringContainsString(
            sprintf(
                '%s wants to share this Wish List',
                $this->customerNameGeneration->getCustomerName($this->customerSession->getCustomerDataObject())
            ),
            $messageContent
        );
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(
                sprintf(
                    "//a[contains(@href, '%s')]/strong[contains(text(), '%s')]",
                    $product->getProductUrl(),
                    $product->getName()
                ),
                $messageContent
            )
        );
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(
                "//a[contains(@href, 'wishlist/shared/index/code/fixture_unique_code/')"
                . " and contains(text(), 'View all Wish List')]",
                $messageContent
            )
        );
    }

    /**
     * @magentoConfigFixture current_store wishlist/email/number_limit 2
     *
     * @return void
     */
    public function testSendWishListWithEmailsLimit(): void
    {
        $this->customerSession->setCustomerId(1);
        $postValues = ['emails' => 'test@example.com, test2@example.com, test3@example.com'];
        $this->dispatchSendWishListRequest($postValues);
        $this->assertResponseWithError('Maximum of 2 emails can be sent.');
    }

    /**
     * @magentoConfigFixture current_store wishlist/email/text_limit 10
     *
     * @return void
     */
    public function testSendWishListWithTextLimit(): void
    {
        $this->customerSession->setCustomerId(1);
        $postValues = ['emails' => 'test@example.com', 'message' => 'Test message'];
        $this->dispatchSendWishListRequest($postValues);
        $this->assertResponseWithError('Message length must not exceed 10 symbols');
    }

    /**
     * @return void
     */
    public function testSendWishListWithoutEmails(): void
    {
        $this->customerSession->setCustomerId(1);
        $postValues = ['emails' => ''];
        $this->dispatchSendWishListRequest($postValues);
        $this->assertResponseWithError('Please enter an email address.');
    }

    /**
     * @return void
     */
    public function testSendWishListWithInvalidEmail(): void
    {
        $this->customerSession->setCustomerId(1);
        $postValues = ['emails' => 'test @example.com'];
        $this->dispatchSendWishListRequest($postValues);
        $this->assertResponseWithError('Please enter a valid email address.');
    }

    /**
     * Dispatch send wish list request.
     *
     * @param array $postValues
     * @return void
     */
    private function dispatchSendWishListRequest(array $postValues): void
    {
        $this->getRequest()->setPostValue($postValues)->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch('wishlist/index/send');
    }

    /**
     * Assert error message and redirect.
     *
     * @param string $message
     * @return void
     */
    private function assertResponseWithError(string $message): void
    {
        $this->assertSessionMessages($this->equalTo([__($message)]), MessageInterface::TYPE_ERROR);
        $this->assertRedirect($this->stringContains('wishlist/index/share'));
    }
}
