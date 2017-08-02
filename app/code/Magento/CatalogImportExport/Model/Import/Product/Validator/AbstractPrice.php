<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Model\Import\Product\Validator;

use Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface;

/**
 * Class \Magento\CatalogImportExport\Model\Import\Product\Validator\AbstractPrice
 *
 * @since 2.0.0
 */
abstract class AbstractPrice extends AbstractImportValidator implements RowValidatorInterface
{
    /**
     * @var \Magento\Customer\Api\GroupRepositoryInterface
     * @since 2.0.0
     */
    protected $groupRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     * @since 2.0.0
     */
    protected $searchCriteriaBuilder;

    /**
     * Customer groups ID-to-name.
     *
     * @var array
     * @since 2.0.0
     */
    protected $customerGroups = [];

    /**
     * @param \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function init($context)
    {
        foreach ($this->groupRepository->getList($this->searchCriteriaBuilder->create())->getItems() as $group) {
            $this->customerGroups[$group->getId()] = true;
        }
        return parent::init($context);
    }
}
