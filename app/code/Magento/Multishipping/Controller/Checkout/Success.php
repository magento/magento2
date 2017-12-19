<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Multishipping\Controller\Checkout;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Multishipping\Model\Checkout\Type\Multishipping;
use Magento\Multishipping\Model\Checkout\Type\Multishipping\State;

/**
 * Multishipping checkout success controller.
 */
class Success extends Action
{
    /**
     * @var State
     */
    private $state;

    /**
     * @var Multishipping
     */
    private $multishipping;

    /**
     * @param Context $context
     * @param State $state
     * @param Multishipping $multishipping
     */
    public function __construct(
        Context $context,
        State $state,
        Multishipping $multishipping
    ) {
        $this->state = $state;
        $this->multishipping = $multishipping;

        parent::__construct($context);
    }

    /**
     * Multishipping checkout success page
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->state->getCompleteStep(State::STEP_OVERVIEW)) {
            $this->_redirect('*/*/addresses');
            return;
        }

        $this->_view->loadLayout();
        $ids = $this->multishipping->getOrderIds();
        $this->_eventManager->dispatch('multishipping_checkout_controller_success_action', ['order_ids' => $ids]);
        $this->_view->renderLayout();
    }
}
