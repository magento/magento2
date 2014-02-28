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

use Magento\Customer\Model\Converter;
use Magento\Customer\Model\Customer as CustomerModel;
use Magento\Exception\InputException;
use Magento\Exception\NoSuchEntityException;
use Magento\Validator\ValidatorException;

/**
 * Manipulate Customer Address Entities *
 */
class CustomerService implements CustomerServiceInterface
{

    /** @var array Cache of DTOs */
    private $_cache = [];

    /**
     * @var Converter
     */
    private $_converter;

    /**
     * @var CustomerMetadataService
     */
    private $_customerMetadataService;


    /**
     * Constructor
     *
     * @param Converter $converter
     * @param CustomerMetadataService $customerMetadataService
     */
    public function __construct(
        Converter $converter,
        CustomerMetadataService $customerMetadataService
    ) {
        $this->_converter = $converter;
        $this->_customerMetadataService = $customerMetadataService;
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomer($customerId)
    {
        if (!isset($this->_cache[$customerId])) {
            $customerModel = $this->_converter->getCustomerModel($customerId);
            $customerEntity = $this->_converter->createCustomerFromModel($customerModel);
            $this->_cache[$customerId] = $customerEntity;
        }

        return $this->_cache[$customerId];
    }


    /**
     * {@inheritdoc}
     */
    public function getCustomerByEmail($customerEmail, $websiteId = null)
    {
        $customerModel = $this->_converter->getCustomerModelByEmail($customerEmail, $websiteId);
        return $this->_converter->createCustomerFromModel($customerModel);
    }

    /**
     * {@inheritdoc}
     */
    public function saveCustomer(Dto\Customer $customer, $password = null)
    {
        $customerModel = $this->_converter->createCustomerModel($customer);

        if ($password) {
            $customerModel->setPassword($password);
        } elseif (!$customerModel->getId()) {
            $customerModel->setPassword($customerModel->generatePassword());
        }

        $this->_validate($customerModel);

        $customerModel->save();
        unset($this->_cache[$customerModel->getId()]);

        return $customerModel->getId();
    }

    /**
     * Validate customer attribute values.
     *
     * @param CustomerModel $customerModel
     * @throws InputException
     * @return void
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function _validate(CustomerModel $customerModel)
    {
        $exception = new InputException();
        if (!\Zend_Validate::is(trim($customerModel->getFirstname()), 'NotEmpty')) {
            $exception->addError(InputException::REQUIRED_FIELD, 'firstname', '');
        }

        if (!\Zend_Validate::is(trim($customerModel->getLastname()), 'NotEmpty')) {
            $exception->addError(InputException::REQUIRED_FIELD, 'lastname', '');
        }

        if (!\Zend_Validate::is($customerModel->getEmail(), 'EmailAddress')) {
            $exception->addError(InputException::INVALID_FIELD_VALUE, 'email', $customerModel->getEmail());
        }

        $dob = $this->_getAttributeMetadata('dob');
        if (!is_null($dob) && $dob->isRequired() && '' == trim($customerModel->getDob())) {
            $exception->addError(InputException::REQUIRED_FIELD, 'dob', '');
        }

        $taxvat = $this->_getAttributeMetadata('taxvat');
        if (!is_null($taxvat) && $taxvat->isRequired() && '' == trim($customerModel->getTaxvat())) {
            $exception->addError(InputException::REQUIRED_FIELD, 'taxvat', '');
        }

        $gender = $this->_getAttributeMetadata('gender');
        if (!is_null($gender) && $gender->isRequired() && '' == trim($customerModel->getGender())) {
            $exception->addError(InputException::REQUIRED_FIELD, 'gender', '');
        }

        if ($exception->getErrors()) {
            throw $exception;
        }
    }

    /**
     * @param $attributeCode
     * @return Dto\Eav\AttributeMetadata|null
     */
    protected function _getAttributeMetadata($attributeCode)
    {
        try {
            return $this->_customerMetadataService->getCustomerAttributeMetadata($attributeCode);
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteCustomer($customerId)
    {
        $customerModel = $this->_converter->getCustomerModel($customerId);
        $customerModel->delete();
        unset($this->_cache[$customerModel->getId()]);
    }
}
