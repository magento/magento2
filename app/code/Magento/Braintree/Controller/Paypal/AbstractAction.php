<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Controller\Paypal;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Braintree\Gateway\Config\PayPal\Config;

/**
 * Abstract class AbstractAction
 */
abstract class AbstractAction extends Action
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Config $config
     * @param Session $checkoutSession
     */
    public function __construct(
        Context $context,
        Config $config,
        Session $checkoutSession
    ) {
        parent::__construct($context);
        $this->config = $config;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Check whether payment method is enabled
     *
     * @inheritdoc
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->config->isActive() || !$this->config->isDisplayShoppingCart()) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);

            /** @var Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('noRoute');

            return $resultRedirect;
        }

        return parent::dispatch($request);
    }

    /**
     * @param CartInterface $quote
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function validateQuote($quote)
    {
        if (!$quote || !$quote->getItemsCount()) {
            throw new \InvalidArgumentException(__('We can\'t initialize checkout.'));
        }
    }
}
