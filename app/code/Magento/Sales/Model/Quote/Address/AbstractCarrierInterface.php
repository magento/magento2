<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Quote\Address;

interface AbstractCarrierInterface
{
    /**
     * Retrieve information from carrier configuration
     *
     * @param   string $field
     * @return  mixed
     */
    public function getConfigData($field);

    /**
     * Collect and get rates
     *
     * @param \Magento\Sales\Model\Quote\Address\RateRequest $request
     * @return \Magento\Framework\Object|bool|null
     */
    public function collectRates(\Magento\Sales\Model\Quote\Address\RateRequest $request);

    /**
     * Do request to shipment
     * Implementation must be in overridden method
     *
     * @param \Magento\Framework\Object $request
     * @return \Magento\Framework\Object
     */
    public function requestToShipment($request);

    /**
     * Do return of shipment
     * Implementation must be in overridden method
     *
     * @param \Magento\Framework\Object $request
     * @return \Magento\Framework\Object
     */
    public function returnOfShipment($request);

    /**
     * Return container types of carrier
     *
     * @param \Magento\Framework\Object|null $params
     * @return array
     */
    public function getContainerTypes(\Magento\Framework\Object $params = null);

    /**
     * Get Container Types, that could be customized
     *
     * @return array
     */
    public function getCustomizableContainerTypes();

    /**
     * Return delivery confirmation types of carrier
     *
     * @param \Magento\Framework\Object|null $params
     * @return array
     */
    public function getDeliveryConfirmationTypes(\Magento\Framework\Object $params = null);

    /**
     * @param \Magento\Sales\Model\Quote\Address\RateRequest $request
     * @return $this|bool|false|\Magento\Framework\Model\AbstractModel
     */
    public function checkAvailableShipCountries(\Magento\Sales\Model\Quote\Address\RateRequest $request);

    /**
     * Processing additional validation to check is carrier applicable.
     *
     * @param \Magento\Sales\Model\Quote\Address\RateRequest $request
     * @return $this|\Magento\Sales\Model\Quote\Address\RateResult\Error|boolean
     */
    public function proccessAdditionalValidation(\Magento\Sales\Model\Quote\Address\RateRequest $request);

    /**
     * Determine whether current carrier enabled for activity
     *
     * @return bool
     */
    public function isActive();

    /**
     * Whether this carrier has fixed rates calculation
     *
     * @return bool
     */
    public function isFixed();

    /**
     * Check if carrier has shipping tracking option available
     *
     * @return bool
     */
    public function isTrackingAvailable();

    /**
     * Check if carrier has shipping label option available
     *
     * @return bool
     */
    public function isShippingLabelsAvailable();

    /**
     *  Retrieve sort order of current carrier
     *
     * @return string|null
     */
    public function getSortOrder();

    /**
     * Get the handling fee for the shipping + cost
     *
     * @param float $cost
     * @return float final price for shipping method
     */
    public function getFinalPriceWithHandlingFee($cost);

    /**
     * Return weight in pounds
     *
     * @param int $weight in someone measure
     * @return float Weight in pounds
     */
    public function convertWeightToLbs($weight);

    /**
     * Set the number of boxes for shipping
     *
     * @param int|float $weight
     * @return int|float weight
     */
    public function getTotalNumOfBoxes($weight);

    /**
     * Is state province required
     *
     * @return bool
     */
    public function isStateProvinceRequired();

    /**
     * Check if city option required
     *
     * @return bool
     */
    public function isCityRequired();

    /**
     * Determine whether zip-code is required for the country of destination
     *
     * @param string|null $countryId
     * @return bool
     */
    public function isZipCodeRequired($countryId = null);

    /**
     * Used to call debug method from not Payment Method context
     *
     * @param mixed $debugData
     * @return void
     */
    public function debugData($debugData);

    /**
     * Getter for carrier code
     *
     * @return string
     */
    public function getCarrierCode();

    /**
     * Return content types of package
     *
     * @param \Magento\Framework\Object $params
     * @return array
     */
    public function getContentTypes(\Magento\Framework\Object $params);
}
