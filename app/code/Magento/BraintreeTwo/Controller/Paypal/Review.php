<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Controller\Paypal;

use Magento\Checkout\Model\Session;
use Magento\Braintree\Controller\PayPal;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\BraintreeTwo\Gateway\Config\PayPal\Config;
use Magento\BraintreeTwo\Model\Paypal\Helper\UpdateQuote;

/**
 * Class Review
 */
class Review extends AbstractAction
{
    /**
     * @var UpdateQuote
     */
    private $updateQuote;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Config $config
     * @param Session $checkoutSession
     * @param UpdateQuote $updateQuote
     */
    public function __construct(
        Context $context,
        Config $config,
        Session $checkoutSession,
        UpdateQuote $updateQuote
    ) {
        parent::__construct($context, $config, $checkoutSession);
        $this->updateQuote = $updateQuote;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $requestData = json_decode(
            $this->getRequest()->getPostValue('result', '{}'),
            true
        );
        $quote = $this->checkoutSession->getQuote();

        try {
            $this->validateQuote($quote);
            $this->validateRequestData($requestData);

            $this->updateQuote->execute(
                $requestData['nonce'],
                $requestData['details'],
                $quote
            );

            /** @var \Magento\Framework\View\Result\Page $resultPage */
            $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

            /** @var \Magento\Braintree\Block\Checkout\Review $reviewBlock */
            $reviewBlock = $resultPage->getLayout()->getBlock('braintreetwo.paypal.review');

            $reviewBlock->setQuote($quote);
            $reviewBlock->getChildBlock('shipping_method')->setData('quote', $quote);

            return $resultPage;
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
        }

        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('checkout/cart');
    }
}
