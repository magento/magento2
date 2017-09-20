<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OneTouchOrdering\Model;

use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\ResourceModel\Quote as QuoteRepository;

class PlaceOrder
{
    /**
     * @var \Magento\Quote\Api\CartManagementInterface
     */
    private $cartManagementInterface;
    /**
     * @var QuoteRepository
     */
    private $quoteRepository;
    /**
     * @var PrepareQuote
     */
    private $prepareQuote;
    /**
     * @var ShippingRateChooserInterface
     */
    private $shippingRateChooser;

    /**
     * PlaceOrder constructor.
     * @param QuoteRepository $quoteRepository
     * @param \Magento\Quote\Api\CartManagementInterface $cartManagementInterface
     * @param PrepareQuote $prepareQuote
     */
    public function __construct(
        QuoteRepository $quoteRepository,
        \Magento\Quote\Api\CartManagementInterface $cartManagementInterface,
        PrepareQuote $prepareQuote,
        ShippingRateChooserInterface $shippingRateChooser
    ) {
        $this->cartManagementInterface = $cartManagementInterface;
        $this->quoteRepository = $quoteRepository;
        $this->prepareQuote = $prepareQuote;
        $this->shippingRateChooser = $shippingRateChooser;
    }

    /**
     * @param Product $product
     * @param CustomerData $customerData
     * @param array $params
     * @return int
     */
    public function placeOrder(Product $product, CustomerData $customerData, array $params): int
    {
        $quote = $this->prepareQuote->prepare($customerData);
        $paramsObject = $this->getProductRequest($params);
        $quote->addProduct($product, $paramsObject);
        $this->shippingRateChooser->choose($quote);
        $this->prepareQuote->preparePayment($quote, $customerData->getCustomerId());
        $this->quoteRepository->save($quote);
        return $this->cartManagementInterface->placeOrder($quote->getId());
    }

    /**
     * @param $requestInfo
     * @return DataObject
     * @throws LocalizedException
     */
    private function getProductRequest($requestInfo): DataObject
    {
        if ($requestInfo instanceof DataObject) {
            $request = $requestInfo;
        } elseif (is_numeric($requestInfo)) {
            $request = new DataObject(['qty' => $requestInfo]);
        } elseif (is_array($requestInfo)) {
            $request = new DataObject($requestInfo);
        } else {
            throw new LocalizedException(
                __('We found an invalid request for adding product to quote.')
            );
        }

        return $request;
    }
}
