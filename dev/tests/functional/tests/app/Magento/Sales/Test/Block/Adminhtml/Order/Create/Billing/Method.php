<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\Create\Billing;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Fixture\InjectableFixture;

/**
 * Adminhtml sales order create payment method block.
 */
class Method extends Block
{
    /**
     * Payment method.
     *
     * @var string
     */
    protected $paymentMethod = '#p_method_%s';

    /**
     * Purchase order number selector.
     *
     * @var string
     */
    protected $purchaseOrderNumber = '#po_number';

    /**
     * Payment form.
     *
     * @var string
     */
    protected $paymentForm = '#payment_form_%s';

    /**
     * Magento loader selector.
     *
     * @var string
     */
    protected $loader = '[data-role=loader]';

    /**
     * Select payment method.
     *
     * @param array $paymentCode
     * @param InjectableFixture|null $creditCard
     */
    public function selectPaymentMethod(array $paymentCode, InjectableFixture $creditCard = null)
    {
        $paymentInput = $this->_rootElement->find(sprintf($this->paymentMethod, $paymentCode['method']));
        if ($paymentInput->isVisible()) {
            $paymentInput->click();
            $this->waitForElementNotVisible($this->loader);
        }
        if (isset($paymentCode['po_number'])) {
            $this->_rootElement->find($this->purchaseOrderNumber)->setValue($paymentCode['po_number']);
        }
        if ($creditCard !== null) {
            $class = explode('\\', get_class($creditCard));
            $module = $class[1];
            /** @var \Magento\Payment\Test\Block\Form\Cc $formBlock */
            $formBlock = $this->blockFactory->create(
                "\\Magento\\{$module}\\Test\\Block\\Form\\Cc",
                ['element' => $this->_rootElement->find('#payment_form_' . $paymentCode['method'])]
            );
            $formBlock->fill($creditCard);
        }
    }
}
