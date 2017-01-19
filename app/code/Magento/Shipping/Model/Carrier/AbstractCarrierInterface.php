<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;

/**
 * Interface AbstractCarrierInterface
 */
interface AbstractCarrierInterface
{
    /**
     * Retrieve information from carrier configuration
     *
     * @param   string $field
     * @return  mixed
     * @api
     */
    public function getConfigData($field);

    /**
     * Collect and get rates
     *
     * @param RateRequest $request
     * @return \Magento\Framework\DataObject|bool|null
     * @api
     */
    public function collectRates(RateRequest $request);

    /**
     * Do request to shipment
     * Implementation must be in overridden method
     *
     * @param \Magento\Framework\DataObject $request
     * @return \Magento\Framework\DataObject
     * @api
     */
    public function requestToShipment($request);

    /**
     * Do return of shipment
     * Implementation must be in overridden method
     *
     * @param \Magento\Framework\DataObject $request
     * @return \Magento\Framework\DataObject
     * @api
     */
    public function returnOfShipment($request);

    /**
     * Return container types of carrier
     *
     * @param \Magento\Framework\DataObject|null $params
     * @return array
     * @api
     */
    public function getContainerTypes(\Magento\Framework\DataObject $params = null);

    /**
     * Get Container Types, that could be customized
     *
     * @return array
     * @api
     */
    public function getCustomizableContainerTypes();

    /**
     * Return delivery confirmation types of carrier
     *
     * @param \Magento\Framework\DataObject|null $params
     * @return array
     * @api
     */
    public function getDeliveryConfirmationTypes(\Magento\Framework\DataObject $params = null);

    /**
     * @param \Magento\Framework\DataObject $request
     * @return $this|bool|false|\Magento\Framework\Model\AbstractModel
     * @api
     */
    public function checkAvailableShipCountries(\Magento\Framework\DataObject $request);

    /**
     * Processing additional validation to check is carrier applicable.
     *
     * @param \Magento\Framework\DataObject $request
     * @return $this|\Magento\Framework\DataObject|boolean
     * @api
     */
    public function proccessAdditionalValidation(\Magento\Framework\DataObject $request);

    /**
     * Determine whether current carrier enabled for activity
     *
     * @return bool
     * @api
     */
    public function isActive();

    /**
     * Whether this carrier has fixed rates calculation
     *
     * @return bool
     * @api
     */
    public function isFixed();

    /**
     * Check if carrier has shipping tracking option available
     *
     * @return bool
     * @api
     */
    public function isTrackingAvailable();

    /**
     * Check if carrier has shipping label option available
     *
     * @return bool
     * @api
     */
    public function isShippingLabelsAvailable();

    /**
     *  Retrieve sort order of current carrier
     *
     * @return string|null
     * @api
     */
    public function getSortOrder();

    /**
     * Get the handling fee for the shipping + cost
     *
     * @param float $cost
     * @return float final price for shipping method
     * @api
     */
    public function getFinalPriceWithHandlingFee($cost);

    /**
     * Set the number of boxes for shipping
     *
     * @param int|float $weight
     * @return int|float weight
     * @api
     */
    public function getTotalNumOfBoxes($weight);

    /**
     * Is state province required
     *
     * @return bool
     * @api
     */
    public function isStateProvinceRequired();

    /**
     * Check if city option required
     *
     * @return bool
     * @api
     */
    public function isCityRequired();

    /**
     * Determine whether zip-code is required for the country of destination
     *
     * @param string|null $countryId
     * @return bool
     * @api
     */
    public function isZipCodeRequired($countryId = null);

    /**
     * Used to call debug method from not Payment Method context
     *
     * @param mixed $debugData
     * @return void
     * @api
     */
    public function debugData($debugData);

    /**
     * Getter for carrier code
     *
     * @return string
     * @api
     */
    public function getCarrierCode();

    /**
     * Return content types of package
     *
     * @param \Magento\Framework\DataObject $params
     * @return array
     * @api
     */
    public function getContentTypes(\Magento\Framework\DataObject $params);
}
