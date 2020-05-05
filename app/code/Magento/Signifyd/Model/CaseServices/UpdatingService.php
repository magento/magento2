<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\CaseServices;

use Magento\Framework\Api\SimpleDataObjectConverter;
use Magento\Framework\Exception\LocalizedException;
use Magento\Signifyd\Api\CaseRepositoryInterface;
use Magento\Signifyd\Api\Data\CaseInterface;
use Magento\Signifyd\Model\CommentsHistoryUpdater;
use Magento\Signifyd\Model\MessageGenerators\GeneratorInterface;
use Magento\Signifyd\Model\OrderStateService;
use Magento\Signifyd\Model\SalesOrderGrid\OrderGridUpdater;

/**
 * Performs Signifyd case entity updating operations.
 *
 * @deprecated 100.3.5 Starting from Magento 2.3.5 Signifyd core integration is deprecated in favor of
 * official Signifyd integration available on the marketplace
 */
class UpdatingService implements UpdatingServiceInterface
{
    /**
     * @var GeneratorInterface
     */
    private $messageGenerator;

    /**
     * @var CaseRepositoryInterface
     */
    private $caseRepository;

    /**
     * @var CommentsHistoryUpdater
     */
    private $commentsHistoryUpdater;

    /**
     * @var \Magento\Signifyd\Model\SalesOrderGrid\OrderGridUpdater
     */
    private $orderGridUpdater;

    /**
     * @var OrderStateService
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
     * @throws LocalizedException
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
     */
    private function setCaseData(CaseInterface $case, array $data)
    {
        // list of keys which should not be replaced, like order id
        $notResolvedKeys = [
            'orderId'
        ];
        foreach ($data as $key => $value) {
            $methodName = 'set' . SimpleDataObjectConverter::snakeCaseToUpperCamelCase($key);
            if (!in_array($key, $notResolvedKeys) && method_exists($case, $methodName)) {
                call_user_func([$case, $methodName], $value);
            }
        }
    }
}
