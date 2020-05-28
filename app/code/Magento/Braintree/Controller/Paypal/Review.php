<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Controller\Paypal;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Braintree\Gateway\Config\PayPal\Config;
use Magento\Braintree\Model\Paypal\Helper\QuoteUpdater;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Payment\Model\Method\Logger;

/**
 * Class Review
 *
 * @deprecated Starting from Magento 2.3.6 Braintree payment method core integration is deprecated
 * in favor of official payment integration available on the marketplace
 */
class Review extends AbstractAction implements HttpPostActionInterface, HttpGetActionInterface
{
    /**
     * @var QuoteUpdater
     */
    private $quoteUpdater;

    /**
     * @var Logger
     */
    private $logger;

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
     * @param Logger $logger
     */
    public function __construct(
        Context $context,
        Config $config,
        Session $checkoutSession,
        QuoteUpdater $quoteUpdater,
        Logger $logger
    ) {
        parent::__construct($context, $config, $checkoutSession);
        $this->quoteUpdater = $quoteUpdater;
        $this->logger = $logger;
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
        $this->logger->debug($requestData);
        $quote = $this->checkoutSession->getQuote();

        try {
            $this->validateQuote($quote);

            if ($requestData && $this->validateRequestData($requestData)) {
                $this->quoteUpdater->execute(
                    $requestData['nonce'],
                    $requestData['details'],
                    $quote
                );
            } elseif (!$quote->getPayment()->getAdditionalInformation(self::$paymentMethodNonce)) {
                throw new LocalizedException(__('Checkout failed to initialize. Verify and try again.'));
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
     * Validate request data
     *
     * @param array $requestData
     * @return boolean
     */
    private function validateRequestData(array $requestData)
    {
        return !empty($requestData['nonce']) && !empty($requestData['details']);
    }
}
