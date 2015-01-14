<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Model\Import\Product\Validator;

use Magento\Framework\Validator\AbstractValidator;
use Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface;

abstract class AbstractPrice extends AbstractValidator implements RowValidatorInterface
{
    /**
     * @var \Magento\Customer\Api\GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * Customer groups ID-to-name.
     *
     * @var array
     */
    protected $customerGroups = [];

    /**
     * @param \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->groupRepository = $groupRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        foreach ($this->groupRepository->getList($this->searchCriteriaBuilder->create())->getItems() as $group) {
            $this->customerGroups[$group->getId()] = true;
        }
        return $this;
    }
}
