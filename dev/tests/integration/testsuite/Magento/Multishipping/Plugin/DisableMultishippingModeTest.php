<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Multishipping\Plugin;

use Magento\Checkout\Model\Cart;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Data\Form\FormKey;
use Magento\TestFramework\TestCase\AbstractController;

/**
 * 'Disable Multishipping' plugin integration tests.
 *
 * @see DisableMultishippingMode
 */
class DisableMultishippingModeTest extends AbstractController
{
    /**
     * @var Cart
     */
    private $cart;

    /**
     * @var FormKey
     */
    private $formKey;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->cart = $this->_objectManager->get(Cart::class);
        $this->formKey = $this->_objectManager->get(FormKey::class);
    }

    /**
     * Test that Quote totals are updated correctly when 'Multishipping' mode is enabled.
     *
     * @magentoDataFixture Magento/Catalog/_files/products.php
     * @return void
     */
    public function testPluginWithEnabledMultishippingMode(): void
    {
        $quote = $this->cart->getQuote();
        $postData = [
            'qty' => '1',
            'product' => '1',
        ];
        $this->getRequest()->setPostValue($postData)
            ->setMethod(HttpRequest::METHOD_POST)
            ->setParam('form_key', $this->formKey->getFormKey());

        $this->dispatch('checkout/cart/add');
        $this->assertEquals(1, (int)$quote->getItemsQty());

        $quote->setTotalsCollectedFlag(false)
            ->setIsMultiShipping(true);

        $this->dispatch('checkout/cart/add');
        $this->assertEquals(2, (int)$quote->getItemsQty());
        $this->assertEquals(0, $quote->getIsMultiShipping());
    }
}
