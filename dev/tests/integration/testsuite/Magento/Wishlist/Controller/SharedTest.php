<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Controller;

use Magento\Framework\App\Request\Http as HttpRequest;

class SharedTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * @magentoDataFixture Magento/Wishlist/_files/wishlist_shared.php
     * @magentoDbIsolation disabled
     * @return void
     */
    public function testAllcartAction()
    {
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setParam('code', 'fixture_unique_code');
        $this->dispatch('wishlist/shared/allcart');

        /** @var \Magento\Checkout\Model\Cart $cart */
        $cart = $this->_objectManager->get(\Magento\Checkout\Model\Cart::class);
        $quoteCount = $cart->getQuote()->getItemsCollection()->count();

        $this->assertEquals(1, $quoteCount);
    }
}
