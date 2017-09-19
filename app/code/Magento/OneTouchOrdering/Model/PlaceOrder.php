<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OneTouchOrdering\Model;

use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\ResourceModel\Quote;

class PlaceOrder
{
    /**
     * @var \Magento\Quote\Api\CartManagementInterface
     */
    private $cartManagementInterface;
    /**
     * @var Quote
     */
    private $quoteRepository;
    /**
     * @var PrepareQuote
     */
    private $prepareQuote;

    /**
     * PlaceOrder constructor.
     * @param Quote $quoteRepository
     * @param \Magento\Quote\Api\CartManagementInterface $cartManagementInterface
     * @param PrepareQuote $prepareQuote
     */
    public function __construct(
        Quote $quoteRepository,
        \Magento\Quote\Api\CartManagementInterface $cartManagementInterface,
        PrepareQuote $prepareQuote
    ) {
        $this->cartManagementInterface = $cartManagementInterface;
        $this->quoteRepository = $quoteRepository;
        $this->prepareQuote = $prepareQuote;
    }

    /**
     * @param Product $product
     * @param array $params
     * @throws LocalizedException
     * @return int
     */
    public function placeOrder(Product $product, array $params)
    {
        $quote = $this->prepareQuote->prepare();
        $paramsObject = $this->_getProductRequest($params);
        $quote->addProduct($product, $paramsObject);
        $this->selectCheapestShippingRate($quote);
        $this->prepareQuote->preparePayment($quote);
        $this->quoteRepository->save($quote);
        return $this->cartManagementInterface->placeOrder($quote->getId());
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @return \Magento\Quote\Model\Quote
     * @throws LocalizedException
     */
    private function selectCheapestShippingRate(\Magento\Quote\Model\Quote $quote)
    {
        if ($quote->isVirtual()) {
            return $quote;
        }

        $address = $quote->getShippingAddress();

        $shippingRates = $address
            ->setCollectShippingRates(true)
            ->collectShippingRates()
            ->getAllShippingRates();
        if (empty($shippingRates)) {
            throw new LocalizedException(
                __('There are no shipping methods available for default shipping address.')
            );
        }

        $rate = array_shift($shippingRates);

        foreach ($shippingRates as $tmpRate) {
            if ($tmpRate['price'] < $rate['price']) {
                $rate = $tmpRate;
            }
        }
        $address->setShippingMethod($rate['code']);

        return $quote;
    }

    /**
     * @param $requestInfo
     * @return DataObject
     * @throws LocalizedException
     */
    private function _getProductRequest($requestInfo)
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
