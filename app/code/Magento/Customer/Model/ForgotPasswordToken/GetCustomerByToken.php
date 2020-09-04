<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\ForgotPasswordToken;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\ExpiredException;
use Magento\Framework\Phrase;

/**
 * Get Customer By reset password token
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class GetCustomerByToken
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param CustomerRepositoryInterface $customerRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->customerRepository = $customerRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Get customer by rp_token
     *
     * @param string $resetPasswordToken
     *
     * @return CustomerInterface
     * @throws ExpiredException
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function execute(string $resetPasswordToken):CustomerInterface
    {
        $this->searchCriteriaBuilder->addFilter(
            'rp_token',
            $resetPasswordToken
        );
        $this->searchCriteriaBuilder->setPageSize(1);
        $found = $this->customerRepository->getList(
            $this->searchCriteriaBuilder->create()
        );

        if ($found->getTotalCount() > 1) {
            //Failed to generated unique RP token
            throw new ExpiredException(
                new Phrase('Reset password token expired.')
            );
        }
        if ($found->getTotalCount() === 0) {
            //Customer with such token not found.
            throw new NoSuchEntityException(
                new Phrase(
                    'No such entity with rp_token = %value',
                    [
                        'value' => $resetPasswordToken
                    ]
                )
            );
        }

        //Unique customer found.
        $items = $found->getItems();

        return reset($items);
    }
}
