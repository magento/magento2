<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InstantPurchase\Model;

use Exception;
use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;

class PlaceOrder
{
    /**
     * @var CartManagementInterface
     */
    private $cartManagementInterface;
    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;
    /**
     * @var QuotePreparer
     */
    private $prepareQuote;
    /**
     * @var ShippingRateChooser
     */
    private $shippingRateChooser;
    /**
     * @var Config
     */
    private $InstantPurchaseConfig;
    /**
     * @var PaymentPreparer
     */
    private $paymentPreparer;

    /**
     * PlaceOrder constructor.
     * @param CartRepositoryInterface $quoteRepository
     * @param CartManagementInterface $cartManagementInterface
     * @param QuotePreparer $prepareQuote
     * @param PaymentPreparer $paymentPreparer
     * @param ShippingRateChooser $shippingRateChooser
     * @param Config $InstantPurchaseConfig
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        CartManagementInterface $cartManagementInterface,
        QuotePreparer $prepareQuote,
        PaymentPreparer $paymentPreparer,
        ShippingRateChooser $shippingRateChooser,
        Config $InstantPurchaseConfig
    ) {
        $this->cartManagementInterface = $cartManagementInterface;
        $this->quoteRepository = $quoteRepository;
        $this->prepareQuote = $prepareQuote;
        $this->shippingRateChooser = $shippingRateChooser;
        $this->InstantPurchaseConfig = $InstantPurchaseConfig;
        $this->paymentPreparer = $paymentPreparer;
    }

    /**
     * @param Product $product
     * @param CustomerDataGetter $customerData
     * @param array $params
     * @throws Exception
     * @return int
     */
    public function placeOrder(Product $product, CustomerDataGetter $customerData, array $params): int
    {
        $paramsObject = $this->getProductRequest($params);
        $quote = $this->prepareQuote->prepare($customerData, $paramsObject);
        $quote->addProduct($product, $paramsObject);
        $this->shippingRateChooser->choose($quote);
        $this->paymentPreparer->prepare($quote, $customerData->getCustomerId(), $paramsObject->getCustomerCc());
        $this->quoteRepository->save($quote);
        return $this->cartManagementInterface->placeOrder($quote->getId());
    }

    /**
     * @param $requestInfo
     * @return DataObject
     * @throws Exception
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
            throw new Exception(
                __('We found an invalid request for adding product to quote.')
            );
        }
        if (!$this->InstantPurchaseConfig->isSelectAddressEnabled()) {
            $request->unsetData('customer_address');
        }
        return $request;
    }
}
