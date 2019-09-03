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
 * Class UpdateItemQty
 *
 * @package Magento\Checkout\Controller\Cart
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
     * @param Context                  $context           Parent dependency
     * @param RequestQuantityProcessor $quantityProcessor Request quantity
     * @param FormKeyValidator         $formKeyValidator  Form validator
     * @param CheckoutSession          $checkoutSession   Session
     * @param Json                     $json              Json serializer
     * @param LoggerInterface          $logger            Logger
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
        $this->formKeyValidator  = $formKeyValidator;
        $this->checkoutSession   = $checkoutSession;
        $this->json              = $json;
        $this->logger            = $logger;
        parent::__construct($context);
    }//end __construct()

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
            $quote    = $this->checkoutSession->getQuote();

            foreach ($cartData as $itemId => $itemInfo) {
                $item = $quote->getItemById($itemId);
                $qty  = isset($itemInfo['qty']) ? (double) $itemInfo['qty'] : 0;
                if ($item) {
                    $this->updateItemQuantity($item, $qty);
                }
            }

            $this->jsonResponse();
        } catch (LocalizedException $e) {
            $this->jsonResponse($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            $this->jsonResponse('Something went wrong while saving the page. Please refresh the page and try again.');
        }//end try
    }//end execute()

    /**
     * Updates quote item quantity.
     *
     * @param Item  $item
     * @param float $qty
     *
     * @throws LocalizedException
     *
     * @return void
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
    }//end updateItemQuantity()

    /**
     * JSON response builder.
     *
     * @param string $error
     *
     * @return void
     */
    private function jsonResponse(string $error = '')
    {
        $this->getResponse()->representJson(
            $this->json->serialize($this->getResponseData($error))
        );
    }//end jsonResponse()

    /**
     * Returns response data.
     *
     * @param string $error
     *
     * @return array
     */
    private function getResponseData(string $error = ''): array
    {
        $response = ['success' => true];

        if (!empty($error)) {
            $response = [
                'success'       => false,
                'error_message' => $error,
            ];
        }

        return $response;
    }//end getResponseData()

    /**
     * Validates the Request HTTP method
     *
     * @throws NotFoundException
     *
     * @return void
     */
    private function validateRequest()
    {
        if ($this->getRequest()->isPost() === false) {
            throw new NotFoundException(__('Page Not Found'));
        }
    }//end validateRequest()

    /**
     * Validates form key
     *
     * @throws LocalizedException
     *
     * @return void
     */
    private function validateFormKey()
    {
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            throw new LocalizedException(
                __('Something went wrong while saving the page. Please refresh the page and try again.')
            );
        }
    }//end validateFormKey()

    /**
     * Validates cart data
     *
     * @param array|null $cartData
     *
     * @throws LocalizedException
     *
     * @return void
     */
    private function validateCartData($cartData = null)
    {
        if (!is_array($cartData)) {
            throw new LocalizedException(
                __('Something went wrong while saving the page. Please refresh the page and try again.')
            );
        }
    }//end validateCartData()
}//end class
