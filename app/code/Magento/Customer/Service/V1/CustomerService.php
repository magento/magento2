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

use Magento\Customer\Service\Entity\V1\Exception;
use Magento\Customer\Model\Customer;
use Magento\Validator\ValidatorException;

/**
 * Manipulate Customer Address Entities *
 */
class CustomerService implements CustomerServiceInterface
{

    /** @var array Cache of DTOs */
    private $_cache = [];


    /**
     * @var \Magento\Customer\Model\Converter
     */
    private $_converter;


    /**
     * Constructor
     *
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Customer\Model\AddressFactory $addressFactory
     * @param \Magento\Customer\Service\V1\CustomerMetadataServiceInterface $eavMetadataService
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Math\Random $mathRandom
     * @param \Magento\Customer\Model\Converter $converter
     * @param \Magento\Customer\Model\Metadata\Validator $validator
     * @param \Magento\Customer\Service\V1\Dto\RegionBuilder $regionBuilder
     * @param \Magento\Customer\Service\V1\Dto\AddressBuilder $addressBuilder
     * @param \Magento\Customer\Service\V1\Dto\Response\CreateCustomerAccountResponseBuilder $createCustomerAccountResponseBuilder
     */
    public function __construct(
        \Magento\Customer\Model\Converter $converter
    ) {
        $this->_converter = $converter;
    }


    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function saveCustomer(Dto\Customer $customer, $password = null)
    {
        $customerModel = $this->_converter->createCustomerModel($customer);

        if ($password) {
            $customerModel->setPassword($password);
        }

        $validationErrors = $customerModel->validate();
        if ($validationErrors !== true) {
            throw new Exception(
                'There were one or more errors validating the customer object.',
                Exception::CODE_VALIDATION_FAILED,
                new ValidatorException([$validationErrors])
            );
        }

        try {
            $customerModel->save();
            unset($this->_cache[$customerModel->getId()]);
        } catch (\Exception $e) {
            switch ($e->getCode()) {
                case Customer::EXCEPTION_EMAIL_EXISTS:
                    $code = Exception::CODE_EMAIL_EXISTS;
                    break;
                default:
                    $code = Exception::CODE_UNKNOWN;
            }
            throw new Exception($e->getMessage(), $code, $e);
        }

        return $customerModel->getId();
    }
}
