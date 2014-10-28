<?php
/**
 *
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Customer\Model\Address;

use Magento\Customer\Model\AddressFactory;
use Magento\Customer\Service\V1\AddressMetadataServiceInterface;
use Magento\Customer\Service\V1\Data\Address;
use Magento\Customer\Service\V1\Data\AddressBuilder;
use Magento\Customer\Model\Address as AddressModel;
use Magento\Customer\Model\Address\AbstractAddress;
use Magento\Customer\Service\V1\Data\Region;
use Magento\Customer\Service\V1\Data\AddressConverter;

/**
 * Customer Address Model converter.
 *
 * Converts a Customer Address Model to a Data Object.
 *
 * TODO: Remove this class after service refactoring is done and the model is no longer needed outside of service.
 *       Then this function could be moved to the service.
 */
class Converter
{
    /**
     * @var AddressBuilder
     */
    protected $_addressBuilder;

    /**
     * @var AddressFactory
     */
    protected $_addressFactory;

    /**
     * Customer metadata service
     *
     * @var AddressMetadataServiceInterface
     */
    protected $_addressMetadataService;

    /**
     * @param AddressBuilder $addressBuilder
     * @param AddressFactory $addressFactory
     * @param AddressMetadataServiceInterface $addressMetadataService
     */
    public function __construct(
        AddressBuilder $addressBuilder,
        AddressFactory $addressFactory,
        AddressMetadataServiceInterface $addressMetadataService
    ) {
        $this->_addressBuilder = $addressBuilder;
        $this->_addressFactory = $addressFactory;
        $this->_addressMetadataService = $addressMetadataService;
    }

    /**
     * Creates an address model out of an address Data Object.
     *
     * @param Address $addressDataObject
     * @return AddressModel
     */
    public function createAddressModel(Address $addressDataObject)
    {
        $addressModel = $this->_addressFactory->create();
        $this->updateAddressModel($addressModel, $addressDataObject);

        return $addressModel;
    }

    /**
     * Updates an Address Model based on information from an Address Data Object.
     *
     * @param AddressModel $addressModel
     * @param Address $address
     * @return void
     */
    public function updateAddressModel(AddressModel $addressModel, Address $address)
    {
        // Set all attributes
        $attributes = AddressConverter::toFlatArray($address);
        foreach ($attributes as $attributeCode => $attributeData) {
            if (Address::KEY_REGION === $attributeCode && $address->getRegion() instanceof Region) {
                $addressModel->setDataUsingMethod(Region::KEY_REGION, $address->getRegion()->getRegion());
                $addressModel->setDataUsingMethod(Region::KEY_REGION_CODE, $address->getRegion()->getRegionCode());
                $addressModel->setDataUsingMethod(Region::KEY_REGION_ID, $address->getRegion()->getRegionId());
            } else {
                $addressModel->setDataUsingMethod($attributeCode, $attributeData);
            }
        }
        // Set customer related data
        $isBilling = $address->isDefaultBilling();
        $addressModel->setIsDefaultBilling($isBilling);
        $addressModel->setIsDefaultShipping($address->isDefaultShipping());
        // Need to use attribute set or future updates can cause data loss
        if (!$addressModel->getAttributeSetId()) {
            $addressModel->setAttributeSetId(AddressMetadataServiceInterface::ATTRIBUTE_SET_ID_ADDRESS);
        }
    }

    /**
     * Make address Data Object out of an address model
     *
     * @param AbstractAddress $addressModel
     * @param int $defaultBillingId
     * @param int $defaultShippingId
     * @return Address
     */
    public function createAddressFromModel(AbstractAddress $addressModel, $defaultBillingId, $defaultShippingId)
    {
        $addressId = $addressModel->getId();

        $attributes = $this->_addressMetadataService->getAllAttributesMetadata();
        $addressData = array();
        foreach ($attributes as $attribute) {
            $code = $attribute->getAttributeCode();
            if (!is_null($addressModel->getData($code))) {
                $addressData[$code] = $addressModel->getData($code);
            }
        }

        $isDefaultBilling = $addressModel->getData('is_default_billing') === null && intval($addressId)
            ? $addressId === $defaultBillingId
            : $addressModel->getData('is_default_billing');

        $isDefaultShipping = $addressModel->getData('is_default_shipping') === null && intval($addressId)
            ? $addressId === $defaultShippingId
            : $addressModel->getData('is_default_shipping');

        $this->_addressBuilder->populateWithArray(
            array_merge(
                $addressData,
                array(
                    Address::KEY_STREET => $addressModel->getStreet(),
                    Address::KEY_DEFAULT_BILLING => $isDefaultBilling,
                    Address::KEY_DEFAULT_SHIPPING => $isDefaultShipping,
                    Address::KEY_REGION => array(
                        Region::KEY_REGION => $addressModel->getRegion(),
                        Region::KEY_REGION_ID => $addressModel->getRegionId(),
                        Region::KEY_REGION_CODE => $addressModel->getRegionCode()
                    )
                )
            )
        );

        if ($addressId) {
            $this->_addressBuilder->setId($addressId);
        }

        if ($addressModel->getCustomerId() || $addressModel->getParentId()) {
            $customerId = $addressModel->getCustomerId() ?: $addressModel->getParentId();
            $this->_addressBuilder->setCustomerId($customerId);
        }

        $addressDataObject = $this->_addressBuilder->create();
        return $addressDataObject;
    }
}
