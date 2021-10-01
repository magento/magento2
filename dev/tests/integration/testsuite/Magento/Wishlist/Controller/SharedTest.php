<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Wishlist\Controller;

use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\TestFramework\TestCase\AbstractController;

class SharedTest extends AbstractController
{
    /**
     * @magentoDataFixture Magento/Wishlist/_files/wishlist_shared.php
     * @magentoDbIsolation disabled
     * @return void
     */
    public function testAllcartAction()
    {
        $this->getRequest()->setParam('code', 'fixture_unique_code');
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch('wishlist/shared/allcart');

        /** @var \Magento\Checkout\Model\Cart $cart */
        $cart = $this->_objectManager->get(\Magento\Checkout\Model\Cart::class);
        $quoteCount = $cart->getQuote()->getItemsCollection()->count();

        $this->assertEquals(1, $quoteCount);
    }
}
