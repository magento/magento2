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
use Magento\Customer\Model\Address\Converter as AddressConverter;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\InputException;

/**
 * Service related to Customer Address related functions
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerAddressService implements CustomerAddressServiceInterface
{
    /**
     * @var AddressConverter
     */
    private $addressConverter;

    /**
     * Directory data
     *
     * @var \Magento\Directory\Helper\Data
     */
    protected $directoryData;

    /**
     * @var \Magento\Customer\Model\AddressRegistry
     */
    protected $addressRegistry;

    /**
     * @var CustomerRegistry
     */
    protected $customerRegistry;

    /**
     * Constructor
     *
     * @param \Magento\Customer\Model\AddressRegistry $addressRegistry
     * @param AddressConverter $addressConverter
     * @param CustomerRegistry $customerRegistry
     * @param \Magento\Directory\Helper\Data $directoryData
     */
    public function __construct(
        \Magento\Customer\Model\AddressRegistry $addressRegistry,
        AddressConverter $addressConverter,
        CustomerRegistry $customerRegistry,
        \Magento\Directory\Helper\Data $directoryData
    ) {
        $this->addressRegistry = $addressRegistry;
        $this->addressConverter = $addressConverter;
        $this->customerRegistry = $customerRegistry;
        $this->directoryData = $directoryData;
    }

    /**
     * {@inheritdoc}
     */
    public function getAddresses($customerId)
    {
        $customer = $this->customerRegistry->retrieve($customerId);
        $addresses = $customer->getAddresses();
        $defaultBillingId = $customer->getDefaultBilling();
        $defaultShippingId = $customer->getDefaultShipping();

        $result = array();
        /** @var $address CustomerAddressModel */
        foreach ($addresses as $address) {
            $result[] = $this->addressConverter->createAddressFromModel(
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
        $customer = $this->customerRegistry->retrieve($customerId);
        $address = $customer->getDefaultBillingAddress();
        if ($address === false) {
            return null;
        }
        return $this->addressConverter->createAddressFromModel(
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
        $customer = $this->customerRegistry->retrieve($customerId);
        $address = $customer->getDefaultShippingAddress();
        if ($address === false) {
            return null;
        }
        return $this->addressConverter->createAddressFromModel(
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
        $address = $this->addressRegistry->retrieve($addressId);
        $customer = $this->customerRegistry->retrieve($address->getCustomerId());

        return $this->addressConverter->createAddressFromModel(
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
        $address = $this->addressRegistry->retrieve($addressId);
        $address->delete();
        $this->addressRegistry->remove($addressId);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function saveAddresses($customerId, $addresses)
    {
        $customerModel = $this->customerRegistry->retrieve($customerId);
        $addressModels = [];

        $inputException = new InputException();
        for ($i = 0; $i < count($addresses); $i++) {
            $address = $addresses[$i];
            $addressModel = null;
            if ($address->getId()) {
                $addressModel = $customerModel->getAddressItemById($address->getId());
            }

            if (is_null($addressModel)) {
                $addressModel = $this->addressConverter->createAddressModel($address);
                $addressModel->setCustomer($customerModel);
            } else {
                $this->addressConverter->updateAddressModel($addressModel, $address);
            }

            $inputException = $this->_validate($addressModel, $inputException, $i);
            $addressModels[] = $addressModel;
        }

        $this->customerRegistry->remove($customerId);
        
        if ($inputException->wasErrorAdded()) {
            throw $inputException;
        }
        $addressIds = array();

        /** @var \Magento\Customer\Model\Address $addressModel */
        foreach ($addressModels as $addressModel) {
            $addressModel->save();
            $this->addressRegistry->remove($addressModel->getId());
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
            $addressModel = $this->addressConverter->createAddressModel($address);
            $inputException = $this->_validate($addressModel, $inputException, $key);
        }
        if ($inputException->wasErrorAdded()) {
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
            $exception->addError(InputException::REQUIRED_FIELD, ['fieldName' => 'firstname', 'index' => $index]);
        }

        if (!\Zend_Validate::is($customerAddressModel->getLastname(), 'NotEmpty')) {
            $exception->addError(InputException::REQUIRED_FIELD, ['fieldName' => 'lastname', 'index' => $index]);
        }

        if (!\Zend_Validate::is($customerAddressModel->getStreet(1), 'NotEmpty')) {
            $exception->addError(InputException::REQUIRED_FIELD, ['fieldName' => 'street', 'index' => $index]);
        }

        if (!\Zend_Validate::is($customerAddressModel->getCity(), 'NotEmpty')) {
            $exception->addError(InputException::REQUIRED_FIELD, ['fieldName' => 'city', 'index' => $index]);
        }

        if (!\Zend_Validate::is($customerAddressModel->getTelephone(), 'NotEmpty')) {
            $exception->addError(InputException::REQUIRED_FIELD, ['fieldName' => 'telephone', 'index' => $index]);
        }

        $havingOptionalZip = $this->directoryData->getCountriesWithOptionalZip();
        if (!in_array($customerAddressModel->getCountryId(), $havingOptionalZip)
            && !\Zend_Validate::is($customerAddressModel->getPostcode(), 'NotEmpty')
        ) {
            $exception->addError(InputException::REQUIRED_FIELD, ['fieldName' => 'postcode', 'index' => $index]);
        }

        if (!\Zend_Validate::is($customerAddressModel->getCountryId(), 'NotEmpty')) {
            $exception->addError(InputException::REQUIRED_FIELD, ['fieldName' => 'countryId', 'index' => $index]);
        }

        if ($customerAddressModel->getCountryModel()->getRegionCollection()->getSize()
            && !\Zend_Validate::is($customerAddressModel->getRegionId(), 'NotEmpty')
            && $this->directoryData->isRegionRequired($customerAddressModel->getCountryId())
        ) {
            $exception->addError(InputException::REQUIRED_FIELD, ['fieldName' => 'regionId', 'index' => $index]);
        }

        return $exception;
    }
}
