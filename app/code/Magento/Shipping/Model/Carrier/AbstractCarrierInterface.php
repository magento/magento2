<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Model\Carrier;

use Magento\Framework\DataObject;
use Magento\Framework\Model\AbstractModel;
use Magento\Quote\Model\Quote\Address\RateRequest;

/**
 * Interface AbstractCarrierInterface
 *
 * @api
 */
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
     * @param RateRequest $request
     * @return DataObject|bool|null
     */
    public function collectRates(RateRequest $request);

    /**
     * Do request to shipment - Implementation must be in overridden method
     *
     * @param DataObject $request
     * @return DataObject
     */
    public function requestToShipment($request);

    /**
     * Do return of shipment - Implementation must be in overridden method
     *
     * @param DataObject $request
     * @return DataObject
     */
    public function returnOfShipment($request);

    /**
     * Return container types of carrier
     *
     * @param DataObject|null $params
     * @return array
     */
    public function getContainerTypes(DataObject $params = null);

    /**
     * Get Container Types, that could be customized
     *
     * @return array
     */
    public function getCustomizableContainerTypes();

    /**
     * Return delivery confirmation types of carrier
     *
     * @param DataObject|null $params
     * @return array
     */
    public function getDeliveryConfirmationTypes(DataObject $params = null);

    /**
     * Validate request for available ship countries
     *
     * @param DataObject $request
     * @return $this|bool|false|AbstractModel
     */
    public function checkAvailableShipCountries(DataObject $request);

    /**
     * Processing additional validation to check is carrier applicable.
     *
     * @param DataObject $request
     * @return $this|DataObject|boolean
     */
    public function proccessAdditionalValidation(DataObject $request);

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
     * @param DataObject $params
     * @return array
     */
    public function getContentTypes(DataObject $params);
}
