<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Service;

use Magento\Sales\Api\ShipmentManagementInterface;

/**
 * Class ShipmentService
 */
class ShipmentService implements ShipmentManagementInterface
{
    /**
     * Repository
     *
     * @var \Magento\Sales\Api\ShipmentCommentRepositoryInterface
     */
    protected $commentRepository;

    /**
     * Search Criteria Builder
     *
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $criteriaBuilder;

    /**
     * Filter Builder
     *
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * Repository
     *
     * @var \Magento\Sales\Api\ShipmentRepositoryInterface
     */
    protected $repository;

    /**
     * Shipment Notifier
     *
     * @var \Magento\Shipping\Model\ShipmentNotifier
     */
    protected $notifier;

    /**
     * Constructor
     *
     * @param \Magento\Sales\Api\ShipmentCommentRepositoryInterface $commentRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Sales\Api\ShipmentRepositoryInterface $repository
     * @param \Magento\Shipping\Model\ShipmentNotifier $notifier
     */
    public function __construct(
        \Magento\Sales\Api\ShipmentCommentRepositoryInterface $commentRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Sales\Api\ShipmentRepositoryInterface $repository,
        \Magento\Shipping\Model\ShipmentNotifier $notifier
    ) {
        $this->commentRepository = $commentRepository;
        $this->criteriaBuilder = $criteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->repository = $repository;
        $this->notifier = $notifier;
    }

    /**
     * Returns shipment label
     *
     * @param int $id
     * @return string
     */
    public function getLabel($id)
    {
        return (string)$this->repository->get($id)->getShippingLabel();
    }

    /**
     * Returns list of comments attached to shipment
     * @param int $id
     * @return \Magento\Sales\Api\Data\ShipmentCommentSearchResultInterface
     */
    public function getCommentsList($id)
    {
        $this->criteriaBuilder->addFilters(
            [$this->filterBuilder->setField('parent_id')->setValue($id)->setConditionType('eq')->create()]
        );
        $searchCriteria = $this->criteriaBuilder->create();
        return $this->commentRepository->getList($searchCriteria);
    }

    /**
     * Notify user
     *
     * @param int $id
     * @return bool
     */
    public function notify($id)
    {
        $shipment = $this->repository->get($id);
        return $this->notifier->notify($shipment);
    }
}
