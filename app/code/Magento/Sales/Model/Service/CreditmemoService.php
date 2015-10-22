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
    protected $commentRepository;

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
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @param \Magento\Sales\Api\CreditmemoRepositoryInterface $creditmemoRepository
     * @param \Magento\Sales\Api\CreditmemoCommentRepositoryInterface $creditmemoCommentRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Sales\Model\Order\CreditmemoNotifier $creditmemoNotifier
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     */
    public function __construct(
        \Magento\Sales\Api\CreditmemoRepositoryInterface $creditmemoRepository,
        \Magento\Sales\Api\CreditmemoCommentRepositoryInterface $creditmemoCommentRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Sales\Model\Order\CreditmemoNotifier $creditmemoNotifier,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        $this->creditmemoRepository = $creditmemoRepository;
        $this->commentRepository = $creditmemoCommentRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->creditmemoNotifier = $creditmemoNotifier;
        $this->priceCurrency = $priceCurrency;
        $this->eventManager = $eventManager;
    }

    /**
     * Cancel an existing creditmemo
     *
     * @param int $id Credit Memo Id
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function cancel($id)
    {
        throw new \Magento\Framework\Exception\LocalizedException(__('You can not cancel Credit Memo'));
        try {
            $creditmemo = $this->creditmemoRepository->get($id);
            $creditmemo->setState(\Magento\Sales\Model\Order\Creditmemo::STATE_CANCELED);
            foreach ($creditmemo->getAllItems() as $item) {
                $item->cancel();
            }
            $this->eventManager->dispatch('sales_order_creditmemo_cancel', ['creditmemo' => $creditmemo]);
            $this->creditmemoRepository->save($creditmemo);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Could not cancel creditmemo'), $e);
        }
        return true;
    }

    /**
     * Returns list of comments attached to creditmemo
     *
     * @param int $id
     * @return \Magento\Sales\Api\Data\CreditmemoCommentSearchResultInterface
     */
    public function getCommentsList($id)
    {
        $this->searchCriteriaBuilder->addFilters(
            [$this->filterBuilder->setField('parent_id')->setValue($id)->setConditionType('eq')->create()]
        );
        $searchCriteria = $this->searchCriteriaBuilder->create();
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
        $creditmemo = $this->creditmemoRepository->get($id);
        return $this->creditmemoNotifier->notify($creditmemo);
    }

    /**
     * Prepare creditmemo to refund and save it.
     *
     * @param \Magento\Sales\Api\Data\CreditmemoInterface $creditmemo
     * @param bool $offlineRequested
     * @return \Magento\Sales\Api\Data\CreditmemoInterface
     */
    public function refund(
        \Magento\Sales\Api\Data\CreditmemoInterface $creditmemo,
        $offlineRequested = false
    ) {
        $this->validateForRefund($creditmemo);
        $creditmemo->setState(\Magento\Sales\Model\Order\Creditmemo::STATE_REFUNDED);

        foreach ($creditmemo->getAllItems() as $item) {
            if ($item->getQty() > 0) {
                $item->register();
            } else {
                $item->isDeleted(true);
            }
        }

        $creditmemo->setDoTransaction(!$offlineRequested);

        $this->eventManager->dispatch('sales_order_creditmemo_refund', ['creditmemo' => $creditmemo]);
        $this->creditmemoRepository->save($creditmemo);
        return $creditmemo;
    }

    /**
     * @param \Magento\Sales\Api\Data\CreditmemoInterface $creditmemo
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function validateForRefund(\Magento\Sales\Api\Data\CreditmemoInterface $creditmemo)
    {
        if ($creditmemo->getId()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('We cannot register an existing credit memo.')
            );
        }

        $baseOrderRefund = $this->priceCurrency->round(
            $creditmemo->getOrder()->getBaseTotalRefunded() + $creditmemo->getBaseGrandTotal()
        );
        if ($baseOrderRefund > $this->priceCurrency->round($creditmemo->getOrder()->getBaseTotalPaid())) {
            $baseAvailableRefund = $creditmemo->getOrder()->getBaseTotalPaid()
                - $creditmemo->getOrder()->getBaseTotalRefunded();

            throw new \Magento\Framework\Exception\LocalizedException(
                __(
                    'The most money available to refund is %1.',
                    $creditmemo->getOrder()->formatBasePrice($baseAvailableRefund)
                )
            );
        }
        return true;
    }
}
