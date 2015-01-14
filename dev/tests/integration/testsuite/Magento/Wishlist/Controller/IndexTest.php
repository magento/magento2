<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Controller;

class IndexTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messages;

    /**
     * @var \Magento\Customer\Helper\View
     */
    protected $_customerViewHelper;

    protected function setUp()
    {
        parent::setUp();
        $logger = $this->getMock('Psr\Log\LoggerInterface', [], [], '', false);
        $this->_customerSession = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Customer\Model\Session',
            [$logger]
        );
        /** @var \Magento\Customer\Api\AccountManagementInterface $service */
        $service = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Customer\Api\AccountManagementInterface'
        );
        $customer = $service->authenticate('customer@example.com', 'password');
        $this->_customerSession->setCustomerDataAsLoggedIn($customer);

        $this->_customerViewHelper = $this->_objectManager->create('Magento\Customer\Helper\View');

        $this->_messages = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\Message\ManagerInterface'
        );
    }

    protected function tearDown()
    {
        $this->_customerSession->logout();
        $this->_customerSession = null;
        parent::tearDown();
    }

    /**
     * Verify wishlist view action
     *
     * The following is verified:
     * - \Magento\Wishlist\Model\Resource\Item\Collection
     * - \Magento\Wishlist\Block\Customer\Wishlist
     * - \Magento\Wishlist\Block\Customer\Wishlist\Items
     * - \Magento\Wishlist\Block\Customer\Wishlist\Item\Column
     * - \Magento\Wishlist\Block\Customer\Wishlist\Item\Column\Cart
     * - \Magento\Wishlist\Block\Customer\Wishlist\Item\Column\Comment
     * - \Magento\Wishlist\Block\Customer\Wishlist\Button
     * - that \Magento\Wishlist\Block\Customer\Wishlist\Item\Options doesn't throw a fatal error
     *
     * @magentoDataFixture Magento/Wishlist/_files/wishlist.php
     */
    public function testItemColumnBlock()
    {
        $this->dispatch('wishlist/index/index');
        $body = $this->getResponse()->getBody();
        $this->assertSelectCount('img[src~="small_image.jpg"][alt="Simple Product"]', 1, $body);
        $this->assertSelectCount('textarea[name~="description"]', 1, $body);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple_xss.php
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testAddActionProductNameXss()
    {
        $this->dispatch('wishlist/index/add/product/1?nocookie=1');
        $messages = $this->_messages->getMessages()->getItems();
        $isProductNamePresent = false;
        foreach ($messages as $message) {
            if (strpos($message->getText(), '&lt;script&gt;alert(&quot;xss&quot;);&lt;/script&gt;') !== false) {
                $isProductNamePresent = true;
            }
            $this->assertNotContains('<script>alert("xss");</script>', (string)$message->getText());
        }
        $this->assertTrue($isProductNamePresent, 'Product name was not found in session messages');
    }

    /**
     * @magentoDataFixture Magento/Wishlist/_files/wishlist_with_product_qty_increments.php
     */
    public function testAllcartAction()
    {
        $formKey = $this->_objectManager->get('Magento\Framework\Data\Form\FormKey')->getFormKey();
        $this->getRequest()->setParam('form_key', $formKey);
        $this->dispatch('wishlist/index/allcart');

        /** @var \Magento\Checkout\Model\Cart $cart */
        $cart = $this->_objectManager->get('Magento\Checkout\Model\Cart');
        $quoteCount = $cart->getQuote()->getItemsCollection()->count();

        $this->assertEquals(0, $quoteCount);
        $this->assertSessionMessages(
            $this->contains('You can buy this product only in increments of 5 for "Simple Product".'),
            \Magento\Framework\Message\MessageInterface::TYPE_ERROR
        );
    }

    /**
     * @magentoDataFixture Magento/Wishlist/_files/wishlist.php
     */
    public function testSendAction()
    {
        \Magento\TestFramework\Helper\Bootstrap::getInstance()
            ->loadArea(\Magento\Framework\App\Area::AREA_FRONTEND);

        $request = [
            'form_key' => $this->_objectManager->get('Magento\Framework\Data\Form\FormKey')->getFormKey(),
            'emails' => 'test@tosend.com',
            'message' => 'message',
            'rss_url' => null, // no rss
        ];

        $this->getRequest()->setPost($request);

        $this->_objectManager->get('Magento\Framework\Registry')->register(
            'wishlist',
            $this->_objectManager->get('Magento\Wishlist\Model\Wishlist')->loadByCustomerId(1)
        );
        $this->dispatch('wishlist/index/send');

        /** @var \Magento\TestFramework\Mail\Template\TransportBuilderMock $transportBuilder */
        $transportBuilder = $this->_objectManager->get('Magento\TestFramework\Mail\Template\TransportBuilderMock');

        $actualResult = \Zend_Mime_Decode::decodeQuotedPrintable(
            $transportBuilder->getSentMessage()->getBodyHtml()->getContent()
        );

        $this->assertStringMatchesFormat(
            '%AThank you, %A'
            . $this->_customerViewHelper->getCustomerName($this->_customerSession->getCustomerDataObject()) . '%A',
            $actualResult
        );
    }
}
