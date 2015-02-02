<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Service;

/**
 * Class CreditmemoService
 */
class CreditmemoService implements \Magento\Sales\Api\CreditmemoManagementInterface
{
    /**
     * @var \Magento\Sales\Api\CreditmemoRepositoryInterface
     */
    protected $creditmemoRepository;

    /**
     * @var \Magento\Sales\Api\CreditmemoCommentRepositoryInterface
     */
    protected $creditmemoCommentRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var \Magento\Sales\Model\Order\CreditmemoNotifier
     */
    protected $creditmemoNotifier;

    /**
     * @param \Magento\Sales\Api\CreditmemoRepositoryInterface $creditmemoRepository
     * @param \Magento\Sales\Api\CreditmemoCommentRepositoryInterface $creditmemoCommentRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Sales\Model\Order\CreditmemoNotifier $creditmemoNotifier
     */
    public function __construct(
        \Magento\Sales\Api\CreditmemoRepositoryInterface $creditmemoRepository,
        \Magento\Sales\Api\CreditmemoCommentRepositoryInterface $creditmemoCommentRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Sales\Model\Order\CreditmemoNotifier $creditmemoNotifier
    ) {
        $this->creditmemoRepository = $creditmemoRepository;
        $this->creditmemoCommentRepository = $creditmemoCommentRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->creditmemoNotifier = $creditmemoNotifier;
    }

    /**
     * Cancel an existing creditmemo
     *
     * @param int $id
     * @return bool
     */
    public function cancel($id)
    {
        return (bool)$this->creditmemoRepository->get($id)->cancel();
    }

    /**
     * Returns list of comments attached to creditmemo
     * @param int $id
     * @return \Magento\Sales\Api\Data\CreditmemoCommentSearchResultInterface
     */
    public function getCommentsList($id)
    {
        $this->searchCriteriaBuilder->addFilter(
            ['eq' => $this->filterBuilder->setField('parent_id')->setValue($id)->create()]
        );
        $criteria = $this->searchCriteriaBuilder->create();
        return $this->creditmemoCommentRepository->getList($criteria);
    }

    /**
     * Notify user
     *
     * @param int $id
     * @return bool
     */
    public function notify($id)
    {
        return $this->creditmemoNotifier->notify($this->creditmemoRepository->get($id));
    }
}
