<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Signifyd\Api\CaseRepositoryInterface;
use Magento\Signifyd\Api\Data\CaseInterface;
use Magento\Signifyd\Model\Validators\CaseDataValidator;

/**
 * Performs Signifyd case entity updating operations.
 */
class CaseUpdatingService implements CaseUpdatingServiceInterface
{
    /**
     * @var MessageGeneratorInterface
     */
    private $messageGenerator;

    /**
     * @var CaseRepositoryInterface
     */
    private $caseRepository;

    /**
     * @var CaseDataValidator
     */
    private $caseDataValidator;

    /**
     * @var CommentsHistoryUpdater
     */
    private $commentsHistoryUpdater;

    /**
     * CaseUpdatingService constructor.
     *
     * @param MessageGeneratorInterface $messageGenerator
     * @param CaseRepositoryInterface $caseRepository
     * @param CaseDataValidator $caseDataValidator
     * @param CommentsHistoryUpdater $commentsHistoryUpdater
     */
    public function __construct(
        MessageGeneratorInterface $messageGenerator,
        CaseRepositoryInterface $caseRepository,
        CaseDataValidator $caseDataValidator,
        CommentsHistoryUpdater $commentsHistoryUpdater
    ) {
        $this->messageGenerator = $messageGenerator;
        $this->caseRepository = $caseRepository;
        $this->caseDataValidator = $caseDataValidator;
        $this->commentsHistoryUpdater = $commentsHistoryUpdater;
    }

    /**
     * Updates Signifyd Case entity by received data.
     *
     * @param array $data
     * @return void
     * @throws LocalizedException
     * @throws NotFoundException
     */
    public function update(array $data)
    {
        if (!$this->caseDataValidator->validate($data)) {
            throw new LocalizedException(__('The "%1" should not be empty.', 'caseId'));
        }

        $case = $this->caseRepository->getByCaseId($data['caseId']);
        if ($case === null) {
            throw new NotFoundException(__('Case entity not found.'));
        }

        try {
            $this->prepareCaseData($case, $data);
            $this->caseRepository->save($case);

            // add comment to order history
            $message = $this->messageGenerator->generate($data);
            $this->commentsHistoryUpdater->addComment($case, $message);
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
    private function prepareCaseData(CaseInterface $case, array $data)
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
