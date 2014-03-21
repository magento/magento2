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
use Magento\Customer\Model\Address\Converter as AddressConverter;

/**
 * Service related to Customer Address related functions
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerAddressService implements CustomerAddressServiceInterface
{
    /**
     * @var \Magento\Customer\Model\AddressFactory
     */
    private $_addressFactory;

    /**
     * @var \Magento\Customer\Model\Converter
     */
    private $_converter;

    /**
     * @var AddressConverter
     */
    private $_addressConverter;

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
     * @param AddressConverter $addressConverter
     * @param \Magento\Directory\Helper\Data $directoryData
     */
    public function __construct(
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Customer\Model\Converter $converter,
        AddressConverter $addressConverter,
        \Magento\Directory\Helper\Data $directoryData
    ) {
        $this->_addressFactory = $addressFactory;
        $this->_converter = $converter;
        $this->_addressConverter = $addressConverter;
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
            $result[] = $this->_addressConverter->createAddressFromModel(
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
        return $this->_addressConverter->createAddressFromModel(
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
        return $this->_addressConverter->createAddressFromModel(
            $address,
            $customer->getDefaultBilling(),
            $customer->getDefaultShipping()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getAddress($addressId)
    {
        //TODO: use cache MAGETWO-16862
        $address = $this->_addressFactory->create();
        $address->load($addressId);
        if (!$address->getId()) {
            throw new NoSuchEntityException('addressId', $addressId);
        }

        $customer = $this->_converter->getCustomerModel($address->getCustomerId());

        return $this->_addressConverter->createAddressFromModel(
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
    public function saveAddresses($customerId, $addresses)
    {
        $customerModel = $this->_converter->getCustomerModel($customerId);
        $addressModels = array();

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
            $this->_addressConverter->updateAddressModel($addressModel, $address);

            $inputException = $this->_validate($addressModel, $inputException, $i);
            $addressModels[] = $addressModel;
        }
        if ($inputException->getErrors()) {
            throw $inputException;
        }
        $addressIds = array();

        /** @var \Magento\Customer\Model\Address $addressModel */
        foreach ($addressModels as $addressModel) {
            $addressModel->save();
            $addressIds[] = $addressModel->getId();
        }

        return $addressIds;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAddresses($addresses)
    {
        $inputException = new InputException();
        foreach ($addresses as $key => $address) {
            $addressModel = $this->_addressConverter->createAddressModel($address);
            $inputException = $this->_validate($addressModel, $inputException, $key);
        }
        if ($inputException->getErrors()) {
            throw $inputException;
        }
        return true;
    }

    /**
     * Validate Customer Addresses attribute values.
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
            $exception->addError(InputException::REQUIRED_FIELD, 'firstname', null, array('index' => $index));
        }

        if (!\Zend_Validate::is($customerAddressModel->getLastname(), 'NotEmpty')) {
            $exception->addError(InputException::REQUIRED_FIELD, 'lastname', null, array('index' => $index));
        }

        if (!\Zend_Validate::is($customerAddressModel->getStreet(1), 'NotEmpty')) {
            $exception->addError(InputException::REQUIRED_FIELD, 'street', null, array('index' => $index));
        }

        if (!\Zend_Validate::is($customerAddressModel->getCity(), 'NotEmpty')) {
            $exception->addError(InputException::REQUIRED_FIELD, 'city', null, array('index' => $index));
        }

        if (!\Zend_Validate::is($customerAddressModel->getTelephone(), 'NotEmpty')) {
            $exception->addError(InputException::REQUIRED_FIELD, 'telephone', null, array('index' => $index));
        }

        $_havingOptionalZip = $this->_directoryData->getCountriesWithOptionalZip();
        if (!in_array(
            $customerAddressModel->getCountryId(),
            $_havingOptionalZip
        ) && !\Zend_Validate::is(
            $customerAddressModel->getPostcode(),
            'NotEmpty'
        )
        ) {
            $exception->addError(InputException::REQUIRED_FIELD, 'postcode', null, array('index' => $index));
        }

        if (!\Zend_Validate::is($customerAddressModel->getCountryId(), 'NotEmpty')) {
            $exception->addError(InputException::REQUIRED_FIELD, 'countryId', null, array('index' => $index));
        }

        if ($customerAddressModel->getCountryModel()->getRegionCollection()->getSize() && !\Zend_Validate::is(
            $customerAddressModel->getRegionId(),
            'NotEmpty'
        ) && $this->_directoryData->isRegionRequired(
            $customerAddressModel->getCountryId()
        )
        ) {
            $exception->addError(InputException::REQUIRED_FIELD, 'regionId', null, array('index' => $index));
        }

        return $exception;
    }
}
