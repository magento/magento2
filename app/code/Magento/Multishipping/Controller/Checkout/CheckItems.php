<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Multishipping\Controller\Checkout;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Multishipping\Controller\Checkout;
use Magento\Multishipping\Helper\Data as MultishippingHelper;
use Magento\Quote\Model\Quote\Item;
use Psr\Log\LoggerInterface;

class CheckItems extends Checkout
{
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var MultishippingHelper
     */
    private $helper;

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
     * @param CustomerSession $customerSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param AccountManagementInterface $accountManagement
     * @param CheckoutSession $checkoutSession
     * @param MultishippingHelper $helper
     * @param Json $json
     * @param LoggerInterface $logger
     */

    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        CustomerRepositoryInterface $customerRepository,
        AccountManagementInterface $accountManagement,
        CheckoutSession $checkoutSession,
        MultishippingHelper $helper,
        Json $json,
        LoggerInterface $logger
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->helper = $helper;
        $this->json = $json;
        $this->logger = $logger;

        parent::__construct(
            $context,
            $customerSession,
            $customerRepository,
            $accountManagement
        );
    }

    /**
     * @return void
     */
    public function execute()
    {
        try {
            $shippingInfo = $this->getRequest()->getPost('ship');
            if (!\is_array($shippingInfo)) {
                throw new LocalizedException(
                    __('We are unable to process your request. Please, try again later.')
                );
            }

            $itemsInfo = $this->collectItemsInfo($shippingInfo);
            $totalQuantity = array_sum($itemsInfo);

            $maxQuantity = $this->helper->getMaximumQty();
            if ($totalQuantity > $maxQuantity) {
                throw new LocalizedException(
                    __('Maximum qty allowed for Shipping to multiple addresses is %1', $maxQuantity)
                );
            }

            $quote = $this->checkoutSession->getQuote();
            foreach ($quote->getAllItems() as $item) {
                if (isset($itemsInfo[$item->getId()])) {
                    $this->updateItemQuantity($item, $itemsInfo[$item->getId()]);
                }
            }

            if ($quote->getHasError()) {
                throw new LocalizedException(__($quote->getMessage()));
            }

            $this->jsonResponse();
        } catch (LocalizedException $e) {
            $this->jsonResponse($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            $this->jsonResponse('We are unable to process your request. Please, try again later.');
        }
    }

    /**
     * Updates quote item quantity.
     *
     * @param Item $item
     * @param float $quantity
     * @throws LocalizedException
     */
    private function updateItemQuantity(Item $item, float $quantity)
    {
        if ($quantity > 0) {
            $item->setQty($quantity);
            if ($item->getHasError()) {
                throw new LocalizedException(__($item->getMessage()));
            }
        }
    }

    /**
     * Group posted items.
     *
     * @param array $shippingInfo
     * @return array
     */
    private function collectItemsInfo(array $shippingInfo): array
    {
        $itemsInfo = [];
        foreach ($shippingInfo as $itemData) {
            if (!\is_array($itemData)) {
                continue;
            }
            foreach ($itemData as $quoteItemId => $data) {
                if (!isset($itemsInfo[$quoteItemId])) {
                    $itemsInfo[$quoteItemId] = 0;
                }
                $itemsInfo[$quoteItemId] += (double)$data['qty'];
            }
        }

        return $itemsInfo;
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
