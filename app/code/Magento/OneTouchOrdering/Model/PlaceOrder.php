<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OneTouchOrdering\Model;

use Magento\Quote\Model\ResourceModel\Quote;

class PlaceOrder
{
    /**
     * @var \Magento\Quote\Api\CartManagementInterface
     */
    protected $cartManagementInterface;
    /**
     * @var CustomerBrainTreeManager
     */
    protected $customerBrainTreeManager;
    /**
     * @var Quote
     */
    protected $quoteRepository;
    /**
     * @var PrepareQuote
     */
    protected $prepareQuote;

    /**
     * PlaceOrder constructor.
     * @param Quote $quoteRepository
     * @param \Magento\Quote\Api\CartManagementInterface $cartManagementInterface
     * @param CustomerBrainTreeManager $customerBrainTreeManager
     * @param PrepareQuote $prepareQuote
     */
    public function __construct(
        \Magento\Quote\Model\ResourceModel\Quote $quoteRepository,
        \Magento\Quote\Api\CartManagementInterface $cartManagementInterface,
        \Magento\OneTouchOrdering\Model\CustomerBrainTreeManager $customerBrainTreeManager,
        \Magento\OneTouchOrdering\Model\PrepareQuote $prepareQuote
    ) {
        $this->cartManagementInterface = $cartManagementInterface;
        $this->customerBrainTreeManager = $customerBrainTreeManager;
        $this->quoteRepository = $quoteRepository;
        $this->prepareQuote = $prepareQuote;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param array $params
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return int
     */
    public function placeOrder(\Magento\Catalog\Model\Product $product, array $params)
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
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function selectCheapestShippingRate(\Magento\Quote\Model\Quote $quote)
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
            throw new \Magento\Framework\Exception\LocalizedException(
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
     * @return \Magento\Framework\DataObject
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getProductRequest($requestInfo)
    {
        if ($requestInfo instanceof \Magento\Framework\DataObject) {
            $request = $requestInfo;
        } elseif (is_numeric($requestInfo)) {
            $request = new \Magento\Framework\DataObject(['qty' => $requestInfo]);
        } elseif (is_array($requestInfo)) {
            $request = new \Magento\Framework\DataObject($requestInfo);
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('We found an invalid request for adding product to quote.')
            );
        }

        return $request;
    }
}
