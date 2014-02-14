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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Customer\Service\V1;

use Magento\Customer\Model\Address as CustomerAddressModel;
use Magento\Exception\NoSuchEntityException;
use Magento\Exception\InputException;

/**
 * Service related to Customer Address related functions
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerAddressService implements CustomerAddressServiceInterface
{
    /** @var \Magento\Customer\Model\AddressFactory */
    private $_addressFactory;

    /**
     * @var \Magento\Customer\Model\Converter
     */
    private $_converter;

    /**
     * @var Dto\RegionBuilder
     */
    private $_regionBuilder;

    /**
     * @var Dto\AddressBuilder
     */
    private $_addressBuilder;

    /**
     * Directory data
     *
     * @var \Magento\Directory\Helper\Data
     */
    protected $_directoryData;

    /**
     * Constructor
     *
     * @param \Magento\Customer\Model\AddressFactory $addressFactory
     * @param \Magento\Customer\Model\Converter $converter
     * @param Dto\RegionBuilder $regionBuilder
     * @param Dto\AddressBuilder $addressBuilder
     * @param \Magento\Directory\Helper\Data $directoryData
     */
    public function __construct(
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Customer\Model\Converter $converter,
        Dto\RegionBuilder $regionBuilder,
        Dto\AddressBuilder $addressBuilder,
        \Magento\Directory\Helper\Data $directoryData
    ) {
        $this->_addressFactory = $addressFactory;
        $this->_converter = $converter;
        $this->_regionBuilder = $regionBuilder;
        $this->_addressBuilder = $addressBuilder;
        $this->_directoryData = $directoryData;
    }

    /**
     * {@inheritdoc}
     */
    public function getAddresses($customerId)
    {
        //TODO: use cache MAGETWO-16862
        $customer = $this->_converter->getCustomerModel($customerId);
        $addresses = $customer->getAddresses();
        $defaultBillingId = $customer->getDefaultBilling();
        $defaultShippingId = $customer->getDefaultShipping();

        $result = array();
        /** @var $address CustomerAddressModel */
        foreach ($addresses as $address) {
            $result[] = $this->_createAddress(
                $address,
                $defaultBillingId,
                $defaultShippingId
            );
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultBillingAddress($customerId)
    {
        //TODO: use cache MAGETWO-16862
        $customer = $this->_converter->getCustomerModel($customerId);
        $address = $customer->getDefaultBillingAddress();
        if ($address === false) {
            return null;
        }
        return $this->_createAddress(
            $address,
            $customer->getDefaultBilling(),
            $customer->getDefaultShipping()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultShippingAddress($customerId)
    {
        //TODO: use cache MAGETWO-16862
        $customer = $this->_converter->getCustomerModel($customerId);
        $address = $customer->getDefaultShippingAddress();
        if ($address === false) {
            return null;
        }
        return $this->_createAddress($address,
            $customer->getDefaultBilling(),
            $customer->getDefaultShipping()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getAddressById($addressId)
    {
        //TODO: use cache MAGETWO-16862
        $address = $this->_addressFactory->create();
        $address->load($addressId);
        if (!$address->getId()) {
            throw new NoSuchEntityException('addressId', $addressId);
        }
        $customer = $this->_converter->getCustomerModel($address->getCustomerId());

        return $this->_createAddress(
            $address,
            $customer->getDefaultBilling(),
            $customer->getDefaultShipping()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function deleteAddress($addressId)
    {
        $address = $this->_addressFactory->create();
        $address->load($addressId);

        if (!$address->getId()) {
            throw new NoSuchEntityException('addressId', $addressId);
        }

        $address->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function saveAddresses($customerId, array $addresses)
    {
        $customerModel = $this->_converter->getCustomerModel($customerId);
        $addressModels = [];

        $inputException = new InputException();
        for ($i = 0; $i < count($addresses); $i++) {
            $address = $addresses[$i];
            $addressModel = null;
            if ($address->getId()) {
                $addressModel = $customerModel->getAddressItemById($address->getId());
            }
            if (is_null($addressModel)) {
                $addressModel = $this->_addressFactory->create();
                $addressModel->setCustomer($customerModel);
            }
            $this->_updateAddressModel($addressModel, $address);

            $inputException = $this->_validate($addressModel, $inputException, $i);
            $addressModels[] = $addressModel;
        }
        if ($inputException->getErrors()) {
            throw $inputException;
        }
        $addressIds = [];

        /** @var \Magento\Customer\Model\Address $addressModel */
        foreach ($addressModels as $addressModel) {
            $addressModel->save();
            $addressIds[] = $addressModel->getId();
        }

        return $addressIds;
    }

    /**
     * Updates an Address Model based on information from an Address DTO.
     *
     * @param CustomerAddressModel $addressModel
     * @param Dto\Address $address
     */
    private function _updateAddressModel(CustomerAddressModel $addressModel, Dto\Address $address)
    {
        // Set all attributes
        foreach ($address->getAttributes() as $attributeCode => $attributeData) {
            if ('region' == $attributeCode
                && $address->getRegion() instanceof Dto\Region
            ) {
                $addressModel->setData('region', $address->getRegion()->getRegion());
                $addressModel->setData('region_code', $address->getRegion()->getRegionCode());
                $addressModel->setData('region_id', $address->getRegion()->getRegionId());
            } else {
                $addressModel->setData($attributeCode, $attributeData);
            }
        }
        // Set customer related data
        $isBilling = $address->isDefaultBilling();
        $addressModel->setIsDefaultBilling($isBilling);
        $addressModel->setIsDefaultShipping($address->isDefaultShipping());
        // Need to use attribute set or future updates can cause data loss
        if (!$addressModel->getAttributeSetId()) {
            $addressModel->setAttributeSetId(CustomerMetadataServiceInterface::ADDRESS_ATTRIBUTE_SET_ID);
        }
    }

    /**
     * Create address based on model
     *
     * @param CustomerAddressModel $addressModel
     * @param int                  $defaultBillingId
     * @param int                  $defaultShippingId
     * @return Dto\Address
     */
    private function _createAddress(
        CustomerAddressModel $addressModel,
        $defaultBillingId,
        $defaultShippingId
    ) {
        $addressId = $addressModel->getId();
        $validAttributes = array_merge(
            $addressModel->getDefaultAttributeCodes(),
            [
                'id',
                'region_id',
                'region',
                'street',
                'vat_is_valid',
                'default_billing',
                'default_shipping',
                //TODO: create VAT object at MAGETWO-16860
                'vat_request_id',
                'vat_request_date',
                'vat_request_success'
            ]
        );
        $addressData = [];
        foreach ($addressModel->getAttributes() as $attribute) {
            $code = $attribute->getAttributeCode();
            if (!in_array($code, $validAttributes) && $addressModel->getData($code) !== null) {
                $addressData[$code] = $addressModel->getData($code);
            }
        }

        $this->_addressBuilder->populateWithArray(
            array_merge(
                $addressData,
                [
                    'street'           => $addressModel->getStreet(),
                    'id'               => $addressId,
                    'default_billing'  => $addressId === $defaultBillingId,
                    'default_shipping' => $addressId === $defaultShippingId,
                    'customer_id'      => $addressModel->getCustomerId(),
                    'region'           => [
                        'region_code' => $addressModel->getRegionCode(),
                        'region' => $addressModel->getRegion(),
                        'region_id' => $addressModel->getRegionId(),
                    ],
                ]
            )
        );

        $retValue = $this->_addressBuilder->create();
        return $retValue;
    }

    /**
     * Validate Customer Addrresss attribute values.
     *
     * @param CustomerAddressModel $customerAddressModel the model to validate
     * @param InputException       $exception            the exception to add errors to
     * @param int                  $index                the index of the address being saved
     * @return InputException
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function _validate(CustomerAddressModel $customerAddressModel, InputException $exception, $index)
    {
        if ($customerAddressModel->getShouldIgnoreValidation()) {
            return $exception;
        }

        if (!\Zend_Validate::is($customerAddressModel->getFirstname(), 'NotEmpty')) {
            $exception->addError(
                InputException::REQUIRED_FIELD,
                'firstname',
                null,
                ['index' => $index]
            );
        }

        if (!\Zend_Validate::is($customerAddressModel->getLastname(), 'NotEmpty')) {
            $exception->addError(
                InputException::REQUIRED_FIELD,
                'lastname',
                null,
                ['index' => $index]
            );
        }

        if (!\Zend_Validate::is($customerAddressModel->getStreet(1), 'NotEmpty')) {
            $exception->addError(
                InputException::REQUIRED_FIELD,
                'street',
                null,
                ['index' => $index]
            );
        }

        if (!\Zend_Validate::is($customerAddressModel->getCity(), 'NotEmpty')) {
            $exception->addError(
                InputException::REQUIRED_FIELD,
                'city',
                null,
                ['index' => $index]
            );
        }

        if (!\Zend_Validate::is($customerAddressModel->getTelephone(), 'NotEmpty')) {
            $exception->addError(
                InputException::REQUIRED_FIELD,
                'telephone',
                null,
                ['index' => $index]
            );
        }

        $_havingOptionalZip = $this->_directoryData->getCountriesWithOptionalZip();
        if (!in_array($customerAddressModel->getCountryId(), $_havingOptionalZip)
            && !\Zend_Validate::is($customerAddressModel->getPostcode(), 'NotEmpty')
        ) {
            $exception->addError(
                InputException::REQUIRED_FIELD,
                'postcode',
                null,
                ['index' => $index]
            );
        }

        if (!\Zend_Validate::is($customerAddressModel->getCountryId(), 'NotEmpty')) {
            $exception->addError(
                InputException::REQUIRED_FIELD,
                'countryId',
                null,
                ['index' => $index]
            );
        }

        if ($customerAddressModel->getCountryModel()->getRegionCollection()->getSize()
            && !\Zend_Validate::is($customerAddressModel->getRegionId(), 'NotEmpty')
            && $this->_directoryData->isRegionRequired($customerAddressModel->getCountryId())
        ) {
            $exception->addError(
                InputException::REQUIRED_FIELD,
                'regionId',
                null,
                ['index' => $index]
            );
        }

        return $exception;
    }
}
