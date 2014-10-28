<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        $logger = $this->getMock('Magento\Framework\Logger', array(), array(), '', false);
        $this->_customerSession = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Customer\Model\Session',
            array($logger)
        );
        $service = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Customer\Service\V1\CustomerAccountService'
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
        $this->_objectManager->configure(
            [
                'Magento\Wishlist\Controller\Index\Send' => [
                    'arguments' => [
                        'transportBuilder' => [
                            'instance' => 'Magento\TestFramework\Mail\Template\TransportBuilderMock'
                        ]
                    ]
                ],
                'preferences' => [
                    'Magento\Framework\Mail\TransportInterface' => 'Magento\TestFramework\Mail\TransportInterfaceMock'
                ]
            ]
        );
        \Magento\TestFramework\Helper\Bootstrap::getInstance()
            ->loadArea(\Magento\Framework\App\Area::AREA_FRONTEND);

        $request = [
            'form_key' => $this->_objectManager->get('Magento\Framework\Data\Form\FormKey')->getFormKey(),
            'emails' => 'test@tosend.com',
            'message' => 'message',
            'rss_url' => null // no rss
        ];

        $this->getRequest()->setPost($request);

        $this->_objectManager->get('Magento\Framework\Registry')->register(
            'wishlist',
            $this->_objectManager->get('Magento\Wishlist\Model\Wishlist')->loadByCustomerId(1)
        );
        $this->dispatch('wishlist/index/send');

        /** @var \Magento\TestFramework\Mail\Template\TransportBuilderMock $transportBuilder */
        $transportBuilder = $this->_objectManager->get('Magento\TestFramework\Mail\Template\TransportBuilderMock');

        $this->assertStringMatchesFormat(
            '%AThank you, %A'
            . $this->_customerViewHelper->getCustomerName($this->_customerSession->getCustomerDataObject()) . '%A',
            $transportBuilder->getSentMessage()->getBodyHtml()->getContent()
        );
    }
}
