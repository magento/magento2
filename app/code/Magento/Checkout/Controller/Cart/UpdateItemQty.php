<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Controller\Cart;

use Magento\Checkout\Model\Cart\RequestQuantityProcessor;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Quote\Model\Quote\Item;
use Psr\Log\LoggerInterface;

class UpdateItemQty extends \Magento\Framework\App\Action\Action
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
     * @param Context $context,
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
     * @return void
     */
    public function execute()
    {
        try {
            if (!$this->formKeyValidator->validate($this->getRequest())) {
                throw new LocalizedException(
                    __('Something went wrong while saving the page. Please refresh the page and try again.')
                );
            }

            $cartData = $this->getRequest()->getParam('cart');
            if (!is_array($cartData)) {
                throw new LocalizedException(
                    __('Something went wrong while saving the page. Please refresh the page and try again.')
                );
            }

            $cartData = $this->quantityProcessor->process($cartData);
            $quote = $this->checkoutSession->getQuote();

            foreach ($cartData as $itemId => $itemInfo) {
                $item = $quote->getItemById($itemId);
                $qty = isset($itemInfo['qty']) ? (double)$itemInfo['qty'] : 0;
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
        }
    }

    /**
     * Updates quote item quantity.
     *
     * @param Item $item
     * @param float $qty
     * @throws LocalizedException
     */
    private function updateItemQuantity(Item $item, float $qty)
    {
        if ($qty > 0) {
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
        $response = [
            'success' => true,
        ];

        if (!empty($error)) {
            $response = [
                'success' => false,
                'error_message' => $error,
            ];
        }

        return $response;
    }
}
