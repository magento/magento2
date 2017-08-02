<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Controller\Hostedpro;

use Magento\Framework\App\Action\Context;
use Magento\Paypal\Helper\Checkout;

/**
 * Class \Magento\Paypal\Controller\Hostedpro\Cancel
 *
 * @since 2.0.0
 */
class Cancel extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Checkout
     * @since 2.0.0
     */
    private $checkoutHelper;

    /**
     * @param Context $context
     * @param Checkout $checkoutHelper
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function execute()
    {
        $this->checkoutHelper->cancelCurrentOrder('');
        $this->checkoutHelper->restoreQuote();

        $this->_redirect('checkout', ['_fragment' => 'payment']);
    }
}
