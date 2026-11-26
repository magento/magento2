<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\ForgotPasswordToken;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\ExpiredException;
use Magento\Framework\Phrase;

/**
 * Get Customer By reset password token
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 * @deprecated Rp Tokens cannot be looked up directly
 * @see not used anymore
 */
class GetCustomerByToken
{
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CollectionFactory|null $collectionFactory
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ?CollectionFactory $collectionFactory = null
    ) {
        $this->customerRepository = $customerRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->collectionFactory = $collectionFactory
            ?? ObjectManager::getInstance()->get(CollectionFactory::class);
    }

    /**
     * Get customer by rp_token
     *
     * @param string $resetPasswordToken
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface
     * @throws ExpiredException
     * @throws NoSuchEntityException
     */
    public function execute(string $resetPasswordToken): CustomerInterface
    {
        $collection = $this->collectionFactory->create();
        $collection->addAttributeToFilter('rp_token', $resetPasswordToken);
        $size = $collection->getSize();

        if ($size > 1) {
            throw new ExpiredException(
                new Phrase('Reset password token expired.')
            );
        }
        if ($size === 0) {
            throw new NoSuchEntityException(
                new Phrase(
                    'No such entity with rp_token = %value',
                    ['value' => $resetPasswordToken]
                )
            );
        }

        return $collection->getFirstItem();
    }
}
