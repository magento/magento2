<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Controller\Paypal;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Braintree\Gateway\Config\PayPal\Config;
use Magento\Braintree\Model\Paypal\Helper\QuoteUpdater;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Review
 */
class Review extends AbstractAction
{
    /**
     * @var QuoteUpdater
     */
    private $quoteUpdater;

    /**
     * @var string
     */
    private static $paymentMethodNonce = 'payment_method_nonce';

    /**
     * Constructor
     *
     * @param Context $context
     * @param Config $config
     * @param Session $checkoutSession
     * @param QuoteUpdater $quoteUpdater
     */
    public function __construct(
        Context $context,
        Config $config,
        Session $checkoutSession,
        QuoteUpdater $quoteUpdater
    ) {
        parent::__construct($context, $config, $checkoutSession);
        $this->quoteUpdater = $quoteUpdater;
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

            if ($this->validateRequestData($requestData)) {
                $this->quoteUpdater->execute(
                    $requestData['nonce'],
                    $requestData['details'],
                    $quote
                );
            } elseif (!$quote->getPayment()->getAdditionalInformation(self::$paymentMethodNonce)) {
                throw new LocalizedException(__('We can\'t initialize checkout.'));
            }

            /** @var \Magento\Framework\View\Result\Page $resultPage */
            $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

            /** @var \Magento\Braintree\Block\Paypal\Checkout\Review $reviewBlock */
            $reviewBlock = $resultPage->getLayout()->getBlock('braintree.paypal.review');

            $reviewBlock->setQuote($quote);
            $reviewBlock->getChildBlock('shipping_method')->setData('quote', $quote);

            return $resultPage;
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
        }

        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('checkout/cart', ['_secure' => true]);
    }

    /**
     * @param array $requestData
     * @return boolean
     */
    private function validateRequestData(array $requestData)
    {
        return !empty($requestData['nonce']) && !empty($requestData['details']);
    }
}
