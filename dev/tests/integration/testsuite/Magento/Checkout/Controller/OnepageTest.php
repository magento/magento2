<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Controller;

/**
 * @magentoDataFixture Magento/Sales/_files/quote.php
 */
class OnepageTest extends \Magento\TestFramework\TestCase\AbstractController
{
    protected function setUp()
    {
        parent::setUp();
        $quote = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Quote\Model\Quote');
        $quote->load('test01', 'reserved_order_id');
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Checkout\Model\Session'
        )->setQuoteId(
            $quote->getId()
        );
    }

    public function testSaveOrderActionWithoutFormKey()
    {
        $this->dispatch('checkout/onepage/saveOrder');
        $this->assertRedirect($this->stringContains('checkout/onepage'));
    }

    public function testSaveOrderActionWithFormKey()
    {
        $formKey = $this->_objectManager->get('Magento\Framework\Data\Form\FormKey');
        $this->getRequest()->setParam('form_key', $formKey->getFormKey());
        $this->dispatch('checkout/onepage/saveOrder');
        $html = $this->getResponse()->getBody();
        $this->assertEquals(
            '{"success":false,"error":true,"error_messages":"Please specify a shipping method."}',
            $html,
            $html
        );
    }
}
