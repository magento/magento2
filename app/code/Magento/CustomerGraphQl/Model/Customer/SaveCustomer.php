<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Customer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\CustomerGraphQl\Model\ValidatorExceptionProcessor;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Exception\GraphQlAlreadyExistsException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Customer\Api\Data\CustomerInterface;

class SaveCustomer
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var ValidatorExceptionProcessor
     */
    private $validatorExceptionProcessor;

    /**
     * @param CustomerRepositoryInterface $customerRepository
     * @param ValidatorExceptionProcessor $validatorExceptionProcessor
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        ValidatorExceptionProcessor $validatorExceptionProcessor
    ) {
        $this->customerRepository = $customerRepository;
        $this->validatorExceptionProcessor = $validatorExceptionProcessor;
    }

    /**
     * Save customer
     *
     * @param CustomerInterface $customer
     * @throws GraphQlAlreadyExistsException
     * @throws GraphQlInputException
     */
    public function execute(CustomerInterface $customer): void
    {
        try {
            $this->customerRepository->save($customer);
        } catch (AlreadyExistsException $e) {
            throw new GraphQlAlreadyExistsException(
                __('A customer with the same email address already exists in an associated website.'),
                $e
            );
        } catch (InputException $e) {
            throw $this->validatorExceptionProcessor->processInputExceptionForGraphQl($e);
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__($e->getMessage()), $e);
        }
    }
}
