<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\CaseServices;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Signifyd\Api\CaseRepositoryInterface;
use Magento\Signifyd\Api\Data\CaseInterface;
use Magento\Signifyd\Model\CommentsHistoryUpdater;
use Magento\Signifyd\Model\MessageGenerators\GeneratorInterface;
use Magento\Signifyd\Model\OrderStateService;
use Magento\Signifyd\Model\SalesOrderGrid\OrderGridUpdater;

/**
 * Performs Signifyd case entity updating operations.
 * @since 2.2.0
 */
class UpdatingService implements UpdatingServiceInterface
{
    /**
     * @var GeneratorInterface
     * @since 2.2.0
     */
    private $messageGenerator;

    /**
     * @var CaseRepositoryInterface
     * @since 2.2.0
     */
    private $caseRepository;

    /**
     * @var CommentsHistoryUpdater
     * @since 2.2.0
     */
    private $commentsHistoryUpdater;

    /**
     * @var \Magento\Signifyd\Model\SalesOrderGrid\OrderGridUpdater
     * @since 2.2.0
     */
    private $orderGridUpdater;

    /**
     * @var OrderStateService
     * @since 2.2.0
     */
    private $orderStateService;

    /**
     * UpdatingService constructor.
     *
     * @param GeneratorInterface $messageGenerator
     * @param CaseRepositoryInterface $caseRepository
     * @param CommentsHistoryUpdater $commentsHistoryUpdater
     * @param \Magento\Signifyd\Model\SalesOrderGrid\OrderGridUpdater $orderGridUpdater
     * @param OrderStateService $orderStateService
     * @since 2.2.0
     */
    public function __construct(
        GeneratorInterface $messageGenerator,
        CaseRepositoryInterface $caseRepository,
        CommentsHistoryUpdater $commentsHistoryUpdater,
        OrderGridUpdater $orderGridUpdater,
        OrderStateService $orderStateService
    ) {
        $this->messageGenerator = $messageGenerator;
        $this->caseRepository = $caseRepository;
        $this->commentsHistoryUpdater = $commentsHistoryUpdater;
        $this->orderGridUpdater = $orderGridUpdater;
        $this->orderStateService = $orderStateService;
    }

    /**
     * Updates Signifyd Case entity by received data.
     *
     * @param CaseInterface $case
     * @param array $data
     * @return void
     * @throws NotFoundException
     * @throws LocalizedException
     * @since 2.2.0
     */
    public function update(CaseInterface $case, array $data)
    {
        if (empty($case->getEntityId()) || empty($case->getCaseId())) {
            throw new LocalizedException(__('The case entity should not be empty.'));
        }

        try {
            $previousDisposition = $case->getGuaranteeDisposition();
            $this->setCaseData($case, $data);
            $orderHistoryComment = $this->messageGenerator->generate($data);
            $case = $this->caseRepository->save($case);
            $this->orderGridUpdater->update($case->getOrderId());
            $this->commentsHistoryUpdater->addComment($case, $orderHistoryComment);
            if ($case->getGuaranteeDisposition() !== $previousDisposition) {
                $this->orderStateService->updateByCase($case);
            }
        } catch (\Exception $e) {
            throw new LocalizedException(__('Cannot update Case entity.'), $e);
        }
    }

    /**
     * Sets data to case entity.
     *
     * @param CaseInterface $case
     * @param array $data
     * @return void
     * @since 2.2.0
     */
    private function setCaseData(CaseInterface $case, array $data)
    {
        // list of keys which should not be replaced, like order id
        $notResolvedKeys = [
            'orderId'
        ];
        foreach ($data as $key => $value) {
            $methodName = 'set' . ucfirst($key);
            if (!in_array($key, $notResolvedKeys) && method_exists($case, $methodName)) {
                call_user_func([$case, $methodName], $value);
            }
        }
    }
}
