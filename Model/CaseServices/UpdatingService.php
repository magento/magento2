<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\CaseServices;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Signifyd\Api\CaseRepositoryInterface;
use Magento\Signifyd\Api\Data\CaseInterface;
use Magento\Signifyd\Model\CommentsHistoryUpdater;
use Magento\Signifyd\Model\MessageGenerators\GeneratorInterface;

/**
 * Performs Signifyd case entity updating operations.
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
     * UpdatingService constructor.
     *
     * @param GeneratorInterface $messageGenerator
     * @param CaseRepositoryInterface $caseRepository
     * @param CommentsHistoryUpdater $commentsHistoryUpdater
     */
    public function __construct(
        GeneratorInterface $messageGenerator,
        CaseRepositoryInterface $caseRepository,
        CommentsHistoryUpdater $commentsHistoryUpdater
    ) {
        $this->messageGenerator = $messageGenerator;
        $this->caseRepository = $caseRepository;
        $this->commentsHistoryUpdater = $commentsHistoryUpdater;
    }

    /**
     * Updates Signifyd Case entity by received data.
     *
     * @param CaseInterface $case
     * @param array $data
     * @return void
     * @throws NotFoundException
     * @throws LocalizedException
     */
    public function update(CaseInterface $case, array $data)
    {
        if (empty($case->getEntityId()) || empty($case->getCaseId())) {
            throw new LocalizedException(__('The case entity should not be empty.'));
        }

        try {
            $this->setCaseData($case, $data);
            $orderHistoryComment = $this->messageGenerator->generate($data);
            $this->caseRepository->save($case);
            $this->commentsHistoryUpdater->addComment($case, $orderHistoryComment);
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
            $methodName = 'set' . ucfirst($key);
            if (!in_array($key, $notResolvedKeys) && method_exists($case, $methodName)) {
                call_user_func([$case, $methodName], $value);
            }
        }
    }
}
