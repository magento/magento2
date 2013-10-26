<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_GoogleCheckout
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\GoogleCheckout\Model\Api\Xml;

class Callback extends \Magento\GoogleCheckout\Model\Api\Xml\AbstractXml
{
    protected $_cachedShippingInfo = array(); // Cache of possible shipping carrier-methods combinations per storeId

    /**
     * Tax data
     *
     * @var \Magento\Tax\Helper\Data
     */
    protected $_taxData = null;

    /**
     * Google checkout data
     *
     * @var \Magento\GoogleCheckout\Helper\Data
     */
    protected $_googleCheckoutData = null;

    /**
     * Core data
     *
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreData = null;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Event\ManagerInterface
     */
    protected $_eventManager = null;

    /**
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\GoogleCheckout\Helper\Data $googleCheckoutData
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\ObjectManager $objectManager
     * @param \Magento\Core\Model\Translate $translator
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\Core\Helper\Data $coreData,
        \Magento\GoogleCheckout\Helper\Data $googleCheckoutData,
        \Magento\Tax\Helper\Data $taxData,
        \Magento\ObjectManager $objectManager,
        \Magento\Core\Model\Translate $translator,
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        array $data = array()
    ) {
        $this->_eventManager = $eventManager;
        $this->_coreData = $coreData;
        $this->_googleCheckoutData = $googleCheckoutData;
        $this->_taxData = $taxData;
        parent::__construct($objectManager, $translator, $coreStoreConfig, $data);
    }

    /**
     * Process notification from google
     * @return \Magento\GoogleCheckout\Model\Api\Xml\Callback
     */
    public function process()
    {
        // Retrieve the XML sent in the HTTP POST request to the ResponseHandler
        $xmlResponse = isset($GLOBALS['HTTP_RAW_POST_DATA']) ?
            $GLOBALS['HTTP_RAW_POST_DATA'] : file_get_contents("php://input");

        $debugData = array('request' => $xmlResponse, 'dir' => 'in');

        if (empty($xmlResponse)) {
            $this->getApi()->debugData($debugData);
            return false;
        }

        list($root, $data) = $this->getGResponse()->GetParsedXML($xmlResponse);

        $this->getGResponse()->SetMerchantAuthentication($this->getMerchantId(), $this->getMerchantKey());
        $status = $this->getGResponse()->HttpAuthentication();

        if (!$status || empty($data[$root])) {
            exit;
        }

        $this->setRootName($root)->setRoot($data[$root]);
        $serialNumber = $this->getData('root/serial-number');
        $this->getGResponse()->setSerialNumber($serialNumber);

        /*
         * Prevent multiple notification processing
         */
        $notification = $this->objectManager->create('Magento\GoogleCheckout\Model\Notification')
            ->setSerialNumber($serialNumber)
            ->loadNotificationData();

        if ($notification->getStartedAt()) {
            if ($notification->isProcessed()) {
                $this->getGResponse()->SendAck();
                return;
            }
            if ($notification->isTimeout()) {
                $notification->updateProcess();
            } else {
                $this->getGResponse()->SendServerErrorStatus();
                return;
            }
        } else {
            $notification->startProcess();
        }

        $method = '_response' . uc_words($root, '', '-');
        if (method_exists($this, $method)) {
            ob_start();

            try {
                $this->$method();
                $notification->stopProcess();
            } catch (\Exception $e) {
                $this->getGResponse()->log->logError($e->__toString());
            }

            $debugData['result'] = ob_get_flush();
            $this->getApi()->debugData($debugData);
        } else {
            $this->getGResponse()->SendBadRequestStatus("Invalid or not supported Message");
        }

        return $this;
    }

    /**
     * Load quote from request and make sure the proper payment method is set
     *
     * @return \Magento\Sales\Model\Quote
     */
    protected function _loadQuote()
    {
        $quoteId = $this->getData('root/shopping-cart/merchant-private-data/quote-id/VALUE');
        $storeId = $this->getData('root/shopping-cart/merchant-private-data/store-id/VALUE');
        $quote = $this->objectManager->create('Magento\Sales\Model\Quote')
            ->setStoreId($storeId)
            ->load($quoteId);
        if ($quote->isVirtual()) {
            $quote->getBillingAddress()->setPaymentMethod('googlecheckout');
        } else {
            $quote->getShippingAddress()->setPaymentMethod('googlecheckout');
        }
        return $quote;
    }

    protected function _getApiUrl()
    {
        return null;
    }

    protected function getGoogleOrderNumber()
    {
        return $this->getData('root/google-order-number/VALUE');
    }

    protected function _responseRequestReceived()
    {

    }

    protected function _responseError()
    {

    }

    protected function _responseDiagnosis()
    {

    }

    protected function _responseCheckoutRedirect()
    {

    }

    /**
     * Calculate available shipping amounts and taxes
     */
    protected function _responseMerchantCalculationCallback()
    {
        $merchantCalculations = new \GoogleMerchantCalculations($this->getCurrency());

        $quote = $this->_loadQuote();

        $billingAddress = $quote->getBillingAddress();
        $address = $quote->getShippingAddress();

        $googleAddress = $this->getData('root/calculate/addresses/anonymous-address');

        $googleAddresses = array();
        if ( isset( $googleAddress['id'] ) ) {
            $googleAddresses[] = $googleAddress;
        } else {
            $googleAddresses = $googleAddress;
        }

        $methods = $this->_coreStoreConfig->getConfig('google/checkout_shipping_merchant/allowed_methods', $this->getStoreId());
        $methods = unserialize($methods);
        $limitCarrier = array();
        foreach ($methods['method'] as $method) {
            if ($method) {
                list($carrierCode, $methodCode) = explode('/', $method);
                $limitCarrier[$carrierCode] = $carrierCode;
            }
        }
        $limitCarrier = array_values($limitCarrier);

        foreach ($googleAddresses as $googleAddress) {
            $addressId = $googleAddress['id'];
            $regionCode = $googleAddress['region']['VALUE'];
            $countryCode = $googleAddress['country-code']['VALUE'];
            $regionModel = $this->objectManager->create('Magento\Directory\Model\Region')->loadByCode($regionCode, $countryCode);
            $regionId = $regionModel->getId();

            $address->setCountryId($countryCode)
                ->setRegion($regionCode)
                ->setRegionId($regionId)
                ->setCity($googleAddress['city']['VALUE'])
                ->setPostcode($googleAddress['postal-code']['VALUE'])
                ->setLimitCarrier($limitCarrier);
            $billingAddress->setCountryId($countryCode)
                ->setRegion($regionCode)
                ->setRegionId($regionId)
                ->setCity($googleAddress['city']['VALUE'])
                ->setPostcode($googleAddress['postal-code']['VALUE'])
                ->setLimitCarrier($limitCarrier);

            $billingAddress->collectTotals();
            $shippingTaxClass = $this->_getTaxClassForShipping($quote);

            $gRequestMethods = $this->getData('root/calculate/shipping/method');
            if ($gRequestMethods) {
                // Make stable format of $gRequestMethods for convenient usage
                if (array_key_exists('VALUE', $gRequestMethods)) {
                    $gRequestMethods = array($gRequestMethods);
                }

                // Form list of mapping Google method names to applicable address rates
                $rates = array();
                $address->setCollectShippingRates(true)
                    ->collectShippingRates();
                foreach ($address->getAllShippingRates() as $rate) {
                    if ($rate instanceof \Magento\Shipping\Model\Rate\Result\Error) {
                        continue;
                    }
                    $methodName = sprintf('%s - %s', $rate->getCarrierTitle(), $rate->getMethodTitle());
                    $rates[$methodName] = $rate;
                }

                foreach ($gRequestMethods as $method) {
                    $result = new \GoogleResult($addressId);
                    $methodName = $method['name'];

                    if (isset($rates[$methodName])) {
                        $rate = $rates[$methodName];

                        $address->setShippingMethod($rate->getCode())
                            ->setLimitCarrier($rate->getCarrier())
                            ->setCollectShippingRates(true)
                            ->collectTotals();
                        $shippingRate = $address->getBaseShippingAmount() - $address->getBaseShippingDiscountAmount();
                        $result->SetShippingDetails($methodName, $shippingRate, 'true');

                        if ($this->getData('root/calculate/tax/VALUE') == 'true') {
                            $taxAmount = $address->getBaseTaxAmount();
                            $taxAmount += $billingAddress->getBaseTaxAmount();
                            $result->setTaxDetails($taxAmount);
                        }
                    } else {
                        if ($shippingTaxClass &&
                            $this->getData('root/calculate/tax/VALUE') == 'true') {
                            $i = 1;
                            $price = $this->_coreStoreConfig->getConfig(
                                'google/checkout_shipping_flatrate/price_'.$i,
                                $quote->getStoreId()
                            );
                            $price = number_format($price, 2, '.', '');
                            $price = (float) $this->_taxData->getShippingPrice($price, false, false);
                            $address->setShippingMethod(null);
                            $address->setCollectShippingRates(true)->collectTotals();
                            $billingAddress->setCollectShippingRates(true)->collectTotals();
                            $address->setBaseShippingAmount($price);
                            $address->setShippingAmount(
                                $this->_reCalculateToStoreCurrency($price, $quote)
                            );
                            $this->_applyShippingTaxClass($address, $shippingTaxClass);
                            $taxAmount = $address->getBaseTaxAmount();
                            $taxAmount += $billingAddress->getBaseTaxAmount();
                            $result->SetShippingDetails(
                                $methodName,
                                $price - $address->getBaseShippingDiscountAmount(),
                                'true'
                            );
                            $result->setTaxDetails($taxAmount);
                            $i++;
                        } else {
                            $result->SetShippingDetails($methodName, 0, 'false');
                        }
                    }
                    $merchantCalculations->AddResult($result);
                }

            } else if ($this->getData('root/calculate/tax/VALUE') == 'true') {
                $address->setShippingMethod(null);
                $address->setCollectShippingRates(true)->collectTotals();
                $billingAddress->setCollectShippingRates(true)->collectTotals();
                if (!$this->_googleCheckoutData->isShippingCarrierActive($this->getStoreId())) {
                    $this->_applyShippingTaxClass($address, $shippingTaxClass);
                }

                $taxAmount = $address->getBaseTaxAmount();
                $taxAmount += $billingAddress->getBaseTaxAmount();

                $result = new \GoogleResult($addressId);
                $result->setTaxDetails($taxAmount);
                $merchantCalculations->addResult($result);
            }
        }

        $this->getGResponse()->ProcessMerchantCalculations($merchantCalculations);
    }

    /**
     * Apply shipping tax class
     *
     * @param \Magento\Object $qAddress
     * @param mixed $shippingTaxClass
     */
    protected function _applyShippingTaxClass($qAddress, $shippingTaxClass)
    {
        if (!$shippingTaxClass) {
            return;
        }

        $quote = $qAddress->getQuote();
        $taxCalculationModel = $this->objectManager->get('Magento\Tax\Model\Calculation');
        $request = $taxCalculationModel->getRateRequest($qAddress);
        $rate = $taxCalculationModel->getRate($request->setProductClassId($shippingTaxClass));

        if (!$this->_taxData->shippingPriceIncludesTax()) {
            $shippingTax    = $qAddress->getShippingAmount() * $rate/100;
            $shippingBaseTax= $qAddress->getBaseShippingAmount() * $rate/100;
        } else {
            $shippingTax    = $qAddress->getShippingTaxAmount();
            $shippingBaseTax= $qAddress->getBaseShippingTaxAmount();
        }

        $shippingTax    = $quote->getStore()->roundPrice($shippingTax);
        $shippingBaseTax= $quote->getStore()->roundPrice($shippingBaseTax);

        $qAddress->setTaxAmount($qAddress->getTaxAmount() + $shippingTax);
        $qAddress->setBaseTaxAmount($qAddress->getBaseTaxAmount() + $shippingBaseTax);
    }

    /**
     * Process new order creation notification from google.
     * Convert customer quote to order
     */
    protected function _responseNewOrderNotification()
    {
        $this->getGResponse()->SendAck();

        // LOOK FOR EXISTING ORDER TO AVOID DUPLICATES
        $orders = $this->objectManager->create('Magento\Sales\Model\Order')->getCollection()
            ->addAttributeToFilter('ext_order_id', $this->getGoogleOrderNumber());
        if (count($orders)) {
            return;
        }

        // IMPORT GOOGLE ORDER DATA INTO QUOTE
        /* @var $quote \Magento\Sales\Model\Quote */
        $quote = $this->_loadQuote();
        $quote->setIsActive(true)->reserveOrderId();

        $this->_eventManager->dispatch('googlecheckout_create_order_before', array('quote' => $quote));
        if ($quote->getErrorMessage()) {
            $this->getGRequest()->SendCancelOrder($this->getGoogleOrderNumber(),
                __('Something went wrong creating the order.'),
                $quote->getErrorMessage()
            );
            return;
        }

        $storeId = $quote->getStoreId();

        $this->objectManager->get('Magento\Core\Model\StoreManager')->setCurrentStore(
            $this->objectManager->get('Magento\Core\Model\StoreManager')->getStore($storeId)
        );
        if ($quote->getQuoteCurrencyCode() != $quote->getBaseCurrencyCode()) {
            $this->objectManager->get('Magento\Core\Model\StoreManager')->getStore()
                ->setCurrentCurrencyCode($quote->getQuoteCurrencyCode());
        }

        $billing = $this->_importGoogleAddress($this->getData('root/buyer-billing-address'));
        $quote->setBillingAddress($billing);

        $shipping = $this->_importGoogleAddress($this->getData('root/buyer-shipping-address'));

        $quote->setShippingAddress($shipping);

        $this->_importGoogleTotals($quote->getShippingAddress());

        $quote->getPayment()->importData(array('method'=>'googlecheckout'));

        $taxMessage = $this->_applyCustomTax($quote->getShippingAddress());

        // CONVERT QUOTE TO ORDER
        $convertQuote = $this->objectManager->get('Magento\Sales\Model\Convert\Quote');

        /* @var $order \Magento\Sales\Model\Order */
        $order = $convertQuote->toOrder($quote);

        if ($quote->isVirtual()) {
            $convertQuote->addressToOrder($quote->getBillingAddress(), $order);
        } else {
            $convertQuote->addressToOrder($quote->getShippingAddress(), $order);
        }

        $order->setExtOrderId($this->getGoogleOrderNumber());
        $order->setExtCustomerId($this->getData('root/buyer-id/VALUE'));

        if (!$order->getCustomerEmail()) {
            $order->setCustomerEmail($billing->getEmail())
                ->setCustomerPrefix($billing->getPrefix())
                ->setCustomerFirstname($billing->getFirstname())
                ->setCustomerMiddlename($billing->getMiddlename())
                ->setCustomerLastname($billing->getLastname())
                ->setCustomerSuffix($billing->getSuffix());
        }

        $order->setBillingAddress($convertQuote->addressToOrderAddress($quote->getBillingAddress()));

        if (!$quote->isVirtual()) {
            $order->setShippingAddress($convertQuote->addressToOrderAddress($quote->getShippingAddress()));
        }
        #$order->setPayment($convertQuote->paymentToOrderPayment($quote->getPayment()));

        foreach ($quote->getAllItems() as $item) {
            $orderItem = $convertQuote->itemToOrderItem($item);
            if ($item->getParentItem()) {
                $orderItem->setParentItem($order->getItemByQuoteItemId($item->getParentItem()->getId()));
            }
            $order->addItem($orderItem);
        }

        /*
         * Adding transaction for correct transaction information displaying on order view at back end.
         * It has no influence on api interaction logic.
         */
        $payment = $this->objectManager->create('Magento\Sales\Model\Order\Payment')
            ->setMethod('googlecheckout')
            ->setTransactionId($this->getGoogleOrderNumber())
            ->setIsTransactionClosed(false);
        $order->setPayment($payment);
        $payment->addTransaction(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_AUTH);
        $order->setCanShipPartiallyItem(false);

        $emailAllowed = ($this->getData('root/buyer-marketing-preferences/email-allowed/VALUE') === 'true');

        $emailStr = $emailAllowed ? __('Yes') : __('No');
        $message = __('Google Order Number: %1', '<strong>' . $this->getGoogleOrderNumber() . '</strong><br />')
                . __('Google Buyer ID: %1', '<strong>' . $this->getData('root/buyer-id/VALUE') . '</strong><br />')
                . __('Is Buyer Willing to Receive Marketing Emails: %1', '<strong>' . $emailStr . '</strong>');
        if ($taxMessage) {
            $message .= __('<br />Warning: <strong>%1</strong><br />', $taxMessage);
        }

        $order->addStatusToHistory($order->getStatus(), $message);
        $order->place();
        $order->save();
        $order->sendNewOrderEmail();
        $this->_eventManager->dispatch('googlecheckout_save_order_after', array('order' => $order));

        $quote->setIsActive(false)->save();

        if ($emailAllowed) {
            $customer = $quote->getCustomer();
            if ($customer && $customer->getId()) {
                $customer->setIsSubscribed(true);
                $this->objectManager->create('Magento\Newsletter\Model\Subscriber')->subscribeCustomer($customer);
            } else {
                $this->objectManager->create('Magento\Newsletter\Model\Subscriber')->subscribe($order->getCustomerEmail());
            }
        }

        $this->_eventManager->dispatch('checkout_submit_all_after', array('order' => $order, 'quote' => $quote));

        $this->getGRequest()->SendMerchantOrderNumber($order->getExtOrderId(), $order->getIncrementId());
    }

    /**
     * If tax value differs tax which is setted on magento,
     * apply Google tax and recollect quote
     *
     * @param \Magento\Object $qAddress
     * @return string | boolean false
     */
    protected function _applyCustomTax($qAddress)
    {
        $quote = $qAddress->getQuote();
        $qTaxAmount = $qAddress->getBaseTaxAmount();
        $newTaxAmount = $this->getData('root/order-adjustment/total-tax/VALUE');

        if ($qTaxAmount != $newTaxAmount) {
            $taxQuotient = (int) $qTaxAmount ? $newTaxAmount/$qTaxAmount : $newTaxAmount;

            $qAddress->setTaxAmount(
                $this->_reCalculateToStoreCurrency($newTaxAmount, $quote)
            );
            $qAddress->setBaseTaxAmount($newTaxAmount);

            $grandTotal = $qAddress->getBaseGrandTotal() - $qTaxAmount + $newTaxAmount;
            $qAddress->setGrandTotal(
                $this->_reCalculateToStoreCurrency($grandTotal, $quote)
            );
            $qAddress->setBaseGrandTotal($grandTotal);

            $subtotalInclTax = $qAddress->getSubtotalInclTax() - $qTaxAmount + $newTaxAmount;
            $qAddress->setSubtotalInclTax($subtotalInclTax);

            foreach ($quote->getAllVisibleItems() as $item) {
                if ($item->getParentItem()) {
                    continue;
                }
                if ($item->getTaxAmount()) {
                    $item->setTaxAmount($item->getTaxAmount()*$taxQuotient);
                    $item->setBaseTaxAmount($item->getBaseTaxAmount()*$taxQuotient);
                    $taxPercent = round(($item->getTaxAmount()/$item->getRowTotal())*100);
                    $item->setTaxPercent($taxPercent);
                }
            }

            $grandTotal = $quote->getBaseGrandTotal() - $qTaxAmount + $newTaxAmount;
            $quote->setGrandTotal(
                $this->_reCalculateToStoreCurrency($grandTotal, $quote)
            );
            $quote->setBaseGrandTotal($grandTotal);

            return __('The tax amount has been applied based on the information received from Google Checkout, '
                . 'because tax amount received from Google Checkout is different from the calculated tax amount');
        }

        return false;
    }

    /**
     * Import address data from google request to address object
     *
     * @param array | \Magento\Object $gAddress
     * @param \Magento\Object $qAddress
     * @return \Magento\Object
     */
    protected function _importGoogleAddress($gAddress, \Magento\Object $qAddress=null)
    {
        if (is_array($gAddress)) {
            $gAddress = new \Magento\Object($gAddress);
        }

        if (!$qAddress) {
            $qAddress = $this->objectManager->create('Magento\Sales\Model\Quote\Address');
        }
        $nameArr = $gAddress->getData('structured-name');
        if ($nameArr) {
            $qAddress->setFirstname($nameArr['first-name']['VALUE'])
                ->setLastname($nameArr['last-name']['VALUE']);
        } else {
            $nameArr = explode(' ', $gAddress->getData('contact-name/VALUE'), 2);
            $qAddress->setFirstname($nameArr[0]);
            if (!empty($nameArr[1])) {
                $qAddress->setLastname($nameArr[1]);
            }
        }
        $region = $this->objectManager->create('Magento\Directory\Model\Region')->loadByCode(
            $gAddress->getData('region/VALUE'),
            $gAddress->getData('country-code/VALUE')
        );

        $qAddress->setCompany($gAddress->getData('company-name/VALUE'))
            ->setEmail($gAddress->getData('email/VALUE'))
            ->setStreet(trim($gAddress->getData('address1/VALUE') . "\n" . $gAddress->getData('address2/VALUE')))
            ->setCity($gAddress->getData('city/VALUE'))
            ->setRegion($gAddress->getData('region/VALUE'))
            ->setRegionId($region->getId())
            ->setPostcode($gAddress->getData('postal-code/VALUE'))
            ->setCountryId($gAddress->getData('country-code/VALUE'))
            ->setTelephone($gAddress->getData('phone/VALUE'))
            ->setFax($gAddress->getData('fax/VALUE'));

        return $qAddress;
    }

    /**
     * Returns array of possible shipping methods combinations
     * Includes internal GoogleCheckout shipping methods, that can be created
     * after successful Google Checkout
     *
     * @param null $storeId
     * @return array
     */
    protected function _getShippingInfos($storeId = null)
    {
        $cacheKey = ($storeId === null) ? 'nofilter' : $storeId;
        if (!isset($this->_cachedShippingInfo[$cacheKey])) {
            /* @var $shipping \Magento\Shipping\Model\Shipping */
            $shipping = $this->objectManager->create('Magento\Shipping\Model\Shipping');
            $carriers = $this->_coreStoreConfig->getConfig('carriers', $storeId);
            $infos = array();

            foreach (array_keys($carriers) as $carrierCode) {
                $carrier = $shipping->getCarrierByCode($carrierCode);
                if (!$carrier) {
                    continue;
                }

                if ($carrierCode == 'googlecheckout') {
                    // Add info about internal google checkout methods
                    $methods = array_merge($carrier->getAllowedMethods(), $carrier->getInternallyAllowedMethods());
                    $carrierName = 'Google Checkout';
                } else {
                    $methods = $carrier->getAllowedMethods();
                    $carrierName = $this->_coreStoreConfig->getConfig('carriers/' . $carrierCode . '/title', $storeId);
                }

                foreach ($methods as $methodCode => $methodName) {
                    $code = $carrierCode . '_' . $methodCode;
                    $name = sprintf('%s - %s', $carrierName, $methodName);
                    $infos[$code] = array(
                        'code' => $code,
                        'name' => $name, // Internal name for google checkout api - to distinguish it in google requests
                        'carrier' => $carrierCode,
                        'carrier_title' => $carrierName,
                        'method' => $methodCode,
                        'method_title' => $methodName
                    );
                }
            }
            $this->_cachedShippingInfo[$cacheKey] = $infos;
        }

        return $this->_cachedShippingInfo[$cacheKey];
    }

    /**
     * Return shipping method code by shipping method name received from Google
     *
     * @param string $name
     * @param int|string|\Magento\Core\Model\Store $storeId
     * @return string| boolean false
     */
    protected function _getShippingMethodByName($name, $storeId = null)
    {
        $code = false;
        $infos = $this->_getShippingInfos($storeId);
        foreach ($infos as $info) {
            if ($info['name'] == $name) {
                $code = $info['code'];
                break;
            }
        }
        return $code;
    }

    /**
     * Creates rate by method code
     * Sets shipping rate's accurate description, titles and so on,
     * so it will get in order description properly
     *
     * @param string $code
     * @param null $storeId
     * @return \Magento\Sales\Model\Quote\Address\Rate
     */
    protected function _createShippingRate($code, $storeId = null)
    {
        $rate = $this->objectManager->create('Magento\Sales\Model\Quote\Address\Rate')
            ->setCode($code);

        $infos = $this->_getShippingInfos($storeId);
        if (isset($infos[$code])) {
            $info = $infos[$code];
            $rate->setCarrier($info['carrier'])
                ->setCarrierTitle($info['carrier_title'])
                ->setMethod($info['method'])
                ->setMethodTitle($info['method_title']);
        }

        return $rate;
    }

    /**
     * Import totals information from google request to quote address
     *
     * @param \Magento\Object $qAddress
     */
    protected function _importGoogleTotals($qAddress)
    {
        $quote = $qAddress->getQuote();
        $qAddress->setTaxAmount(
            $this->_reCalculateToStoreCurrency($this->getData('root/order-adjustment/total-tax/VALUE'), $quote)
        );
        $qAddress->setBaseTaxAmount($this->getData('root/order-adjustment/total-tax/VALUE'));

        $method = null;
        $prefix = 'root/order-adjustment/shipping/';
        if (null !== ($shipping = $this->getData($prefix . 'carrier-calculated-shipping-adjustment'))) {
            $method = 'googlecheckout_carrier';
        } else if (null !== ($shipping = $this->getData($prefix . 'merchant-calculated-shipping-adjustment'))) {
            $method = 'googlecheckout_merchant';
        } else if (null !== ($shipping = $this->getData($prefix . 'flat-rate-shipping-adjustment'))) {
            $method = 'googlecheckout_flatrate';
        } else if (null !== ($shipping = $this->getData($prefix . 'pickup-shipping-adjustment'))) {
            $method = 'googlecheckout_pickup';
        }

        if ($method) {
            $this->objectManager->get('Magento\Tax\Model\Config')->setShippingPriceIncludeTax(false);
            $rate = $this->_createShippingRate($method)
                ->setMethodTitle($shipping['shipping-name']['VALUE'])
                ->setPrice($shipping['shipping-cost']['VALUE']);
            $qAddress->addShippingRate($rate)
                ->setShippingMethod($method)
                ->setShippingDescription($shipping['shipping-name']['VALUE']);
            // We get from Google price with discounts applied via merchant calculations
            $qAddress->setShippingAmountForDiscount(0);

            /*if (!$this->_taxData->shippingPriceIncludesTax($quote->getStore())) {
                $includingTax = $this->_taxData->getShippingPrice(
                    $excludingTax, true, $qAddress, $quote->getCustomerTaxClassId()
                );
                $shippingTax = $includingTax - $excludingTax;
                $qAddress->setShippingTaxAmount($this->_reCalculateToStoreCurrency($shippingTax, $quote))
                    ->setBaseShippingTaxAmount($shippingTax)
                    ->setShippingInclTax($includingTax)
                    ->setBaseShippingInclTax($this->_reCalculateToStoreCurrency($includingTax, $quote));
            } else {
                if ($method == 'googlecheckout_carrier') {
                    $qAddress->setShippingTaxAmount(0)
                        ->setBaseShippingTaxAmount(0);
                }
            }*/
        } else {
            $qAddress->setShippingMethod(null);
        }


        $qAddress->setGrandTotal(
            $this->_reCalculateToStoreCurrency($this->getData('root/order-total/VALUE'), $quote)
        );
        $qAddress->setBaseGrandTotal($this->getData('root/order-total/VALUE'));
    }

    /**
     * Order getter
     *
     * @throws \Magento\Core\Exception
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        if (!$this->hasData('order')) {
            $order = $this->objectManager->create('Magento\Sales\Model\Order')
                ->loadByAttribute('ext_order_id', $this->getGoogleOrderNumber());
            if (!$order->getId()) {
                throw new \Magento\Core\Exception('Invalid Order: ' . $this->getGoogleOrderNumber());
            }
            $this->setData('order', $order);
        }
        return $this->getData('order');
    }

    protected function _responseRiskInformationNotification()
    {
        $this->getGResponse()->SendAck();

        $order = $this->getOrder();
        $payment = $order->getPayment();

        $order
            ->setRemoteIp($this->getData('root/risk-information/ip-address/VALUE'));

        $payment
            ->setCcLast4($this->getData('root/risk-information/partial-cc-number/VALUE'))
            ->setCcAvsStatus($this->getData('root/risk-information/avs-response/VALUE'))
            ->setCcCidStatus($this->getData('root/risk-information/cvn-response/VALUE'));

        $msg = __('Google Risk Information:');
        $msg .= '<br />' . __('IP Address: %1', '<strong>' . $order->getRemoteIp() . '</strong>');
        $msg .= '<br />' . __('CC Partial: xxxx-%1', '<strong>' . $payment->getCcLast4() . '</strong>');
        $msg .= '<br />' . __('AVS Status: %1', '<strong>' . $payment->getCcAvsStatus() . '</strong>');
        $msg .= '<br />' . __('CID Status: %1', '<strong>' . $payment->getCcCidStatus() . '</strong>');
        $msg .= '<br />' . __('Eligible for Protection: %1', '<strong>'
                . ($this->getData('root/risk-information/eligible-for-protection/VALUE')=='true' ? 'Yes' : 'No')
                . '</strong>');
        $msg .= '<br />' . __('Buyer Account Age: %1 days', '<strong>'
                . $this->getData('root/risk-information/buyer-account-age/VALUE') . '</strong>');

        $order->addStatusToHistory($order->getStatus(), $msg);
        $order->save();
    }

    /**
     * Process authorization notification
     */
    protected function _responseAuthorizationAmountNotification()
    {
        $this->getGResponse()->SendAck();

        $order = $this->getOrder();
        $payment = $order->getPayment();

        $payment->setAmountAuthorized($this->getData('root/authorization-amount/VALUE'));

        $expDate = $this->getData('root/authorization-expiration-date/VALUE');
        $expDate = new \Zend_Date($expDate);
        $msg = __('Google Authorization:');
        $msg .= '<br />' . __('Amount: %1', '<strong>'
                . $this->_formatAmount($payment->getAmountAuthorized()) . '</strong>');
        $msg .= '<br />' . __('Expiration: %1', '<strong>' . $expDate->toString() . '</strong>');

        $order->addStatusToHistory($order->getStatus(), $msg);

        $order->setPaymentAuthorizationAmount($payment->getAmountAuthorized());
        $timestamp = $this->objectManager->create('Magento\Core\Model\Date')->gmtTimestamp(
            $this->getData('root/authorization-expiration-date/VALUE')
        );
        $order->setPaymentAuthorizationExpiration(
            $timestamp ? $timestamp : $this->objectManager->create('Magento\Core\Model\Date')->gmtTimestamp()
        );

        $order->save();
    }

    /**
     * Process charge notification
     *
     */
    protected function _responseChargeAmountNotification()
    {
        $this->getGResponse()->SendAck();

        $order = $this->getOrder();
        $payment = $order->getPayment();
        if ($payment->getMethod() !== 'googlecheckout') {
            return;
        }

        $latestCharged = $this->getData('root/latest-charge-amount/VALUE');
        $totalCharged = $this->getData('root/total-charge-amount/VALUE');
        $payment->setAmountCharged($totalCharged);
        $order->setIsInProcess(true);

        $msg = __('Google Charge:');
        $msg .= '<br />' . __('Latest Charge: %1', '<strong>' . $this->_formatAmount($latestCharged) . '</strong>');
        $msg .= '<br />' . __('Total Charged: %1', '<strong>' . $this->_formatAmount($totalCharged) . '</strong>');

        if (!$order->hasInvoices() && abs($order->getBaseGrandTotal() - $latestCharged) < .0001) {
            $invoice = $this->_createInvoice();
            $msg .= '<br />' . __('Invoice Auto-Created: %1', '<strong>' . $invoice->getIncrementId() . '</strong>');
        }

        $this->_addChildTransaction(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE);

        $open = \Magento\Sales\Model\Order\Invoice::STATE_OPEN;
        foreach ($order->getInvoiceCollection() as $orderInvoice) {
            if ($orderInvoice->getState() == $open && $orderInvoice->getBaseGrandTotal() == $latestCharged) {
                $orderInvoice->setState(\Magento\Sales\Model\Order\Invoice::STATE_PAID)
                    ->setTransactionId($this->getGoogleOrderNumber())
                    ->save();
                break;
            }
        }

        $order->addStatusToHistory($order->getStatus(), $msg);
        $order->save();
    }

    protected function _createInvoice()
    {
        $order = $this->getOrder();

        $invoice = $order->prepareInvoice()
            ->setTransactionId($this->getGoogleOrderNumber())
            ->addComment(__('Auto-generated from GoogleCheckout Charge'))
            ->register()
            ->pay();

        $transactionSave = $this->objectManager->create('Magento\Core\Model\Resource\Transaction')
            ->addObject($invoice)
            ->addObject($invoice->getOrder());

        $transactionSave->save();

        return $invoice;
    }

    protected function _createShipment()
    {
        $order = $this->getOrder();
        $shipment = $order->prepareShipment();
        if ($shipment) {
            $shipment->register();

            $order->setIsInProcess(true);

            $transactionSave = $this->objectManager->create('Magento\Core\Model\Resource\Transaction')
                ->addObject($shipment)
                ->addObject($shipment->getOrder())
                ->save();
        }

        return $shipment;
    }

    /**
     * Process chargeback notification
     */
    protected function _responseChargebackAmountNotification()
    {
        $this->getGResponse()->SendAck();

        $latestChargeback = $this->getData('root/latest-chargeback-amount/VALUE');
        $totalChargeback = $this->getData('root/total-chargeback-amount/VALUE');

        $order = $this->getOrder();
        if ($order->getBaseGrandTotal() == $totalChargeback) {
            $creditmemo = $this->objectManager->create('Magento\Sales\Model\Service\Order', array('order' => $order))
                ->prepareCreditmemo()
                ->setPaymentRefundDisallowed(true)
                ->setAutomaticallyCreated(true)
                ->register();

            $creditmemo->addComment(__('Credit memo has been created automatically'));
            $creditmemo->save();
        }
        $msg = __('Google Chargeback:');
        $msg .= '<br />' . __('Latest Chargeback: %1', '<strong>'
                . $this->_formatAmount($latestChargeback) . '</strong>');
        $msg .= '<br />' . __('Total Chargeback: %1', '<strong>'
                . $this->_formatAmount($totalChargeback) . '</strong>');

        $this->_addChildTransaction(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_REFUND,
            \Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE);

        $order->addStatusToHistory($order->getStatus(), $msg);
        $order->save();
    }

    /**
     * Process refund notification
     */
    protected function _responseRefundAmountNotification()
    {
        $this->getGResponse()->SendAck();

        $latestRefunded = $this->getData('root/latest-refund-amount/VALUE');
        $totalRefunded = $this->getData('root/total-refund-amount/VALUE');

        $order = $this->getOrder();
        $amountRefundLeft = $order->getBaseGrandTotal() - $order->getBaseTotalRefunded()
            - $order->getBaseAdjustmentNegative();
        if (abs($amountRefundLeft) < .0001) {
            return;
        }
        if ($amountRefundLeft < $latestRefunded) {
            $latestRefunded = $amountRefundLeft;
            $totalRefunded  = $order->getBaseGrandTotal();
        }

        if ($order->getBaseTotalRefunded() > 0) {
            $adjustment = array('adjustment_positive' => $latestRefunded);
        } else {
            $adjustment = array('adjustment_negative' => $order->getBaseGrandTotal() - $latestRefunded);
        }

        $creditmemo = $this->objectManager->create('Magento\Sales\Model\Service\Order', array('order' => $order))
            ->prepareCreditmemo($adjustment)
            ->setPaymentRefundDisallowed(true)
            ->setAutomaticallyCreated(true)
            ->register()
            ->addComment(__('Credit memo has been created automatically'))
            ->save();

        $msg = __('Google Refund:');
        $msg .= '<br />' . __('Latest Refund: %1', '<strong>' . $this->_formatAmount($latestRefunded) . '</strong>');
        $msg .= '<br />' . __('Total Refunded: %1', '<strong>' . $this->_formatAmount($totalRefunded) . '</strong>');

        $this->_addChildTransaction(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_REFUND,
            \Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE);

        $order->addStatusToHistory($order->getStatus(), $msg);
        $order->save();
    }

    protected function _responseOrderStateChangeNotification()
    {
        $this->getGResponse()->SendAck();

        $prevFinancial = $this->getData('root/previous-financial-order-state/VALUE');
        $newFinancial = $this->getData('root/new-financial-order-state/VALUE');
        $prevFulfillment = $this->getData('root/previous-fulfillment-order-state/VALUE');
        $newFulfillment = $this->getData('root/new-fulfillment-order-state/VALUE');

        $msg = __('Google Order Status Change:');
        if ($prevFinancial!=$newFinancial) {
            $msg .= "<br />" . __('Financial: %1 -> %2', '<strong>' . $prevFinancial . '</strong>',
                    '<strong>' . $newFinancial . '</strong>');
        }
        if ($prevFulfillment!=$newFulfillment) {
            $msg .= "<br />" . __('Fulfillment: %1 -> %2', '<strong>' . $prevFulfillment . '</strong>',
                    '<strong>' . $newFulfillment . '</strong>');
        }
        $this->getOrder()
            ->addStatusToHistory($this->getOrder()->getStatus(), $msg)
            ->save();

        $method = '_orderStateChangeFinancial' . uc_words(strtolower($newFinancial), '', '_');
        if (method_exists($this, $method)) {
            $this->$method();
        }

        $method = '_orderStateChangeFulfillment' . uc_words(strtolower($newFulfillment), '', '_');
        if (method_exists($this, $method)) {
            $this->$method();
        }
    }

    /**
     * Add transaction to payment with defined type
     *
     * @param   string $typeTarget
     * @param   string $typeParent
     * @return  \Magento\GoogleCheckout\Model\Api\Xml\Callback
     */
    protected function _addChildTransaction(
        $typeTarget,
        $typeParent = \Magento\Sales\Model\Order\Payment\Transaction::TYPE_AUTH
    ) {
        $payment                = $this->getOrder()->getPayment();
        $googleOrderId          = $this->getGoogleOrderNumber();
        $parentTransactionId    = $googleOrderId;

        if ($typeParent != \Magento\Sales\Model\Order\Payment\Transaction::TYPE_AUTH) {
            $parentTransactionId .= '-' . $typeParent;
        } else {
            $payment->setIsTransactionClosed(false);
        }

        $parentTransaction = $payment->getTransaction($parentTransactionId);

        if ($parentTransaction) {
            $payment->setParentTransactionId($parentTransactionId)
                ->setTransactionId($googleOrderId . '-' . $typeTarget)
                ->addTransaction($typeTarget);

            if ($this->getOrder()->getTotalDue() < .0001) {
                $parentTransaction->setIsClosed(true)
                    ->save();
            }
        }

        return $this;
    }

    protected function _orderStateChangeFinancialReviewing()
    {

    }

    protected function _orderStateChangeFinancialChargeable()
    {
        #$this->getGRequest()->SendProcessOrder($this->getGoogleOrderNumber());
        #$this->getGRequest()->SendChargeOrder($this->getGoogleOrderNumber(), '');
    }

    protected function _orderStateChangeFinancialCharging()
    {

    }

    protected function _orderStateChangeFinancialCharged()
    {

    }

    protected function _orderStateChangeFinancialPaymentDeclined()
    {

    }

    protected function _orderStateChangeFinancialCancelled()
    {
        $this->getOrder()->setBeingCanceledFromGoogleApi(true)->cancel()->save();
    }

    protected function _orderStateChangeFinancialCancelledByGoogle()
    {
        $this
            ->getOrder()
            ->setBeingCanceledFromGoogleApi(true)
            ->cancel()
            ->save();

        $this
            ->getGRequest()
            ->SendBuyerMessage($this->getGoogleOrderNumber(), "Sorry, your order is cancelled by Google", true);
    }

    protected function _orderStateChangeFulfillmentNew()
    {

    }

    protected function _orderStateChangeFulfillmentProcessing()
    {

    }

    protected function _orderStateChangeFulfillmentDelivered()
    {
        $shipment = $this->_createShipment();
        if (!is_null($shipment))
            $shipment->save();
    }

    protected function _orderStateChangeFulfillmentWillNotDeliver()
    {

    }

    /**
     * Format amount to be displayed
     *
     * @param mixed $amount
     * @return string
     */
    protected function _formatAmount($amount)
    {
        // format currency in currency format, but don't enclose it into <span>
        return $this->_coreData->currency($amount, true, false);
    }
}
