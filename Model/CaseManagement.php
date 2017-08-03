<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Signifyd\Api\CaseManagementInterface;
use Magento\Signifyd\Api\CaseRepositoryInterface;
use Magento\Signifyd\Api\Data\CaseInterface;
use Magento\Signifyd\Api\Data\CaseInterfaceFactory;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\DB\Adapter\DuplicateException;

/**
 *
 * Default case management implementation
 * @since 2.2.0
 */
class CaseManagement implements CaseManagementInterface
{
    /**
     * @var CaseRepositoryInterface
     * @since 2.2.0
     */
    private $caseRepository;

    /**
     * @var CaseInterfaceFactory
     * @since 2.2.0
     */
    private $caseFactory;

    /**
     * @var FilterBuilder
     * @since 2.2.0
     */
    private $filterBuilder;

    /**
     * @var SearchCriteriaBuilder
     * @since 2.2.0
     */
    private $searchCriteriaBuilder;

    /**
     * CaseManagement constructor.
     * @param CaseRepositoryInterface $caseRepository
     * @param CaseInterfaceFactory $caseFactory
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @since 2.2.0
     */
    public function __construct(
        CaseRepositoryInterface $caseRepository,
        CaseInterfaceFactory $caseFactory,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->caseRepository = $caseRepository;
        $this->caseFactory = $caseFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function create($orderId)
    {
        /** @var \Magento\Signifyd\Api\Data\CaseInterface $case */
        $case = $this->caseFactory->create();
        $case->setOrderId($orderId)
            ->setStatus(CaseInterface::STATUS_PENDING)
            ->setGuaranteeDisposition(CaseInterface::GUARANTEE_PENDING);
        try {
            return $this->caseRepository->save($case);
        } catch (DuplicateException $e) {
            throw new AlreadyExistsException(__('This order already has associated case entity'), $e);
        }
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getByOrderId($orderId)
    {
        $filters = [
            $this->filterBuilder->setField('order_id')
                ->setValue($orderId)
                ->create()
        ];
        $searchCriteria = $this->searchCriteriaBuilder->addFilters($filters)->create();
        $items = $this->caseRepository->getList($searchCriteria)->getItems();
        return !empty($items) ? array_pop($items) : null;
    }
}
