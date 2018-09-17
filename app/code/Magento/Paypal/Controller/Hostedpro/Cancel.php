<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Controller\Hostedpro;

use Magento\Framework\App\Action\Context;
use Magento\Paypal\Helper\Checkout;

class Cancel extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Checkout
     */
    private $checkoutHelper;

    /**
     * @param Context $context
     * @param Checkout $checkoutHelper
     */
    public function __construct(
        Context $context,
        Checkout $checkoutHelper
    ) {
        parent::__construct($context);
        $this->checkoutHelper = $checkoutHelper;
    }

    /**
     * Customer canceled payment on gateway side.
     *
     * @return void
     */
    public function execute()
    {
        $this->checkoutHelper->cancelCurrentOrder('');
        $this->checkoutHelper->restoreQuote();

        $this->_redirect('checkout', ['_fragment' => 'payment']);
    }
}
