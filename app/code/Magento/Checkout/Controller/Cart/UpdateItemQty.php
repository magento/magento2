<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Controller\Cart;

use Magento\Checkout\Model\Cart\RequestQuantityProcessor;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Model\Quote\Item;
use Psr\Log\LoggerInterface;

/**
 * UpdateItemQty ajax request
 */
class UpdateItemQty extends Action implements HttpPostActionInterface
{

    /**
     * @var RequestQuantityProcessor
     */
    private $quantityProcessor;

    /**
     * @var FormKeyValidator
     */
    private $formKeyValidator;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * UpdateItemQty constructor
     *
     * @param Context $context
     * @param RequestQuantityProcessor $quantityProcessor
     * @param FormKeyValidator $formKeyValidator
     * @param CheckoutSession $checkoutSession
     * @param Json $json
     * @param LoggerInterface $logger
     */

    public function __construct(
        Context $context,
        RequestQuantityProcessor $quantityProcessor,
        FormKeyValidator $formKeyValidator,
        CheckoutSession $checkoutSession,
        Json $json,
        LoggerInterface $logger
    ) {
        $this->quantityProcessor = $quantityProcessor;
        $this->formKeyValidator = $formKeyValidator;
        $this->checkoutSession = $checkoutSession;
        $this->json = $json;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Controller execute method
     *
     * @return void
     */
    public function execute()
    {
        try {
            $this->validateRequest();
            $this->validateFormKey();

            $cartData = $this->getRequest()->getParam('cart');

            $this->validateCartData($cartData);

            $cartData = $this->quantityProcessor->process($cartData);
            $quote = $this->checkoutSession->getQuote();

            $response = [];
            foreach ($cartData as $itemId => $itemInfo) {
                $item = $quote->getItemById($itemId);
                $qty = isset($itemInfo['qty']) ? (double) $itemInfo['qty'] : 0;
                if ($item) {
                    try {
                        $this->updateItemQuantity($item, $qty);
                    } catch (LocalizedException $e) {
                        $response[] = [
                            'error' => $e->getMessage(),
                            'itemId' => $itemId
                        ];
                    }
                }
            }

            $this->jsonResponse(count($response)? json_encode($response) : '');
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            $this->jsonResponse('Something went wrong while saving the page. Please refresh the page and try again.');
        }
    }

    /**
     * Updates quote item quantity.
     *
     * @param Item $item
     * @param float $qty
     * @return void
     * @throws LocalizedException
     */
    private function updateItemQuantity(Item $item, float $qty)
    {
        if ($qty > 0) {
            $item->clearMessage();
            $item->setQty($qty);

            if ($item->getHasError()) {
                throw new LocalizedException(__($item->getMessage()));
            }
        }
    }

    /**
     * JSON response builder.
     *
     * @param string $error
     * @return void
     */
    private function jsonResponse(string $error = '')
    {
        $this->getResponse()->representJson(
            $this->json->serialize($this->getResponseData($error))
        );
    }

    /**
     * Returns response data.
     *
     * @param string $error
     * @return array
     */
    private function getResponseData(string $error = ''): array
    {
        $response = ['success' => true];

        if (!empty($error)) {
            $response = [
                'success' => false,
                'error_message' => $error,
            ];
        }

        return $response;
    }

    /**
     * Validates the Request HTTP method
     *
     * @return void
     * @throws NotFoundException
     */
    private function validateRequest()
    {
        if ($this->getRequest()->isPost() === false) {
            throw new NotFoundException(__('Page Not Found'));
        }
    }

    /**
     * Validates form key
     *
     * @return void
     * @throws LocalizedException
     */
    private function validateFormKey()
    {
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            throw new LocalizedException(
                __('Something went wrong while saving the page. Please refresh the page and try again.')
            );
        }
    }

    /**
     * Validates cart data
     *
     * @param array|null $cartData
     * @return void
     * @throws LocalizedException
     */
    private function validateCartData($cartData = null)
    {
        if (!is_array($cartData)) {
            throw new LocalizedException(
                __('Something went wrong while saving the page. Please refresh the page and try again.')
            );
        }
    }
}
