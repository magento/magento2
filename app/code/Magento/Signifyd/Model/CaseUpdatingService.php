<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Sales\Model\Order\Status\HistoryFactory;
use Magento\Signifyd\Api\CaseManagementInterface;
use Magento\Signifyd\Api\CaseRepositoryInterface;
use Magento\Signifyd\Model\Validators\CaseDataValidator;
use Psr\Log\LoggerInterface;

/**
 * Performs Signifyd case entity updating operations.
 */
class CaseUpdatingService
{
    /**
     * @var MessageGeneratorInterface
     */
    private $messageGenerator;

    /**
     * @var CaseManagementInterface
     */
    private $caseManagement;

    /**
     * @var CaseRepositoryInterface
     */
    private $caseRepository;

    /**
     * @var HistoryFactory
     */
    private $historyFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CaseDataValidator
     */
    private $caseDataValidator;

    /**
     * CaseUpdatingService constructor.
     *
     * @param MessageGeneratorInterface $messageGenerator
     * @param CaseManagementInterface $caseManagement
     * @param CaseRepositoryInterface $caseRepository
     * @param LoggerInterface $logger
     * @param CaseDataValidator $caseDataValidator
     * @param HistoryFactory $historyFactory
     */
    public function __construct(
        MessageGeneratorInterface $messageGenerator,
        CaseManagementInterface $caseManagement,
        CaseRepositoryInterface $caseRepository,
        LoggerInterface $logger,
        CaseDataValidator $caseDataValidator,
        HistoryFactory $historyFactory
    ) {
        $this->messageGenerator = $messageGenerator;
        $this->caseManagement = $caseManagement;
        $this->caseRepository = $caseRepository;
        $this->historyFactory = $historyFactory;
        $this->logger = $logger;
        $this->caseDataValidator = $caseDataValidator;
    }

    /**
     * Updates Signifyd Case entity by received data.
     *
     * @param DataObject $data
     * @throws LocalizedException
     * @throws NotFoundException
     */
    public function update(DataObject $data)
    {
        if (!$this->caseDataValidator->validate($data)) {
            throw new LocalizedException(__('The "%1" should not be empty.', 'caseId'));
        }

        $case = $this->caseManagement->getByCaseId($data->getData('caseId'));
        if ($case === null) {
            throw new NotFoundException(__('Case entity not found.'));
        }

        try {
            $case->setGuaranteeEligible($data->getData('guaranteeEligible'))
                ->setStatus($data->getData('status'))
                ->setReviewDisposition($data->getData('reviewDisposition'))
                ->setAssociatedTeam($data->getData('associatedTeam'))
                ->setCreatedAt($data->getData('createdAt'))
                ->setUpdatedAt($data->getData('updatedAt'))
                ->setScore($data->getData('score'))
                ->setGuaranteeDisposition($data->getData('guaranteeDisposition'));
            $this->caseRepository->save($case);

            // add comment to order history
            $message = $this->messageGenerator->generate($data);
            /** @var \Magento\Sales\Api\Data\OrderStatusHistoryInterface $history */
            $history = $this->historyFactory->create();
            $history->setParentId($case->getOrderId())
                ->setComment($message)
                ->save();

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new LocalizedException(__('Cannot update Case entity.'), $e);
        }
    }
}
