<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Service;

use Magento\Sales\Api\ShipmentManagementInterface;

/**
 * Class ShipmentService
 * @since 2.0.0
 */
class ShipmentService implements ShipmentManagementInterface
{
    /**
     * Repository
     *
     * @var \Magento\Sales\Api\ShipmentCommentRepositoryInterface
     * @since 2.0.0
     */
    protected $commentRepository;

    /**
     * Search Criteria Builder
     *
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     * @since 2.0.0
     */
    protected $criteriaBuilder;

    /**
     * Filter Builder
     *
     * @var \Magento\Framework\Api\FilterBuilder
     * @since 2.0.0
     */
    protected $filterBuilder;

    /**
     * Repository
     *
     * @var \Magento\Sales\Api\ShipmentRepositoryInterface
     * @since 2.0.0
     */
    protected $repository;

    /**
     * Shipment Notifier
     *
     * @var \Magento\Shipping\Model\ShipmentNotifier
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getLabel($id)
    {
        return (string)$this->repository->get($id)->getShippingLabel();
    }

    /**
     * Returns list of comments attached to shipment
     * @param int $id
     * @return \Magento\Sales\Api\Data\ShipmentCommentSearchResultInterface
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function notify($id)
    {
        $shipment = $this->repository->get($id);
        return $this->notifier->notify($shipment);
    }
}
