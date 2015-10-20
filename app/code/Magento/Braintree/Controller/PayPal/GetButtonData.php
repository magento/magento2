<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Controller\PayPal;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class GetButtonData
 */
class GetButtonData extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Session $checkoutSession
     */
    public function __construct(Context $context, Session $checkoutSession)
    {
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @return ResultInterface
     * @throws LocalizedException
     */
    public function execute()
    {
        $isAjax = $this->_request->getParam('isAjax');

        if (!$isAjax) {
            throw new LocalizedException(__('Wrong type of request.'));
        }

        $items = $this->checkoutSession->getQuote()->getAllItems();
        $response = [
            'isEmpty' => 0 === count($items),
            'amount' => $this->checkoutSession->getQuote()->getBaseGrandTotal(),
            'currency' => $this->checkoutSession->getQuote()->getCurrency()->getBaseCurrencyCode(),
        ];

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($response);

        return $resultJson;
    }
}
