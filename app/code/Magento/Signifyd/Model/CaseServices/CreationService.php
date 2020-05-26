<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\CaseServices;

use Magento\Signifyd\Api\CaseCreationServiceInterface;
use Magento\Signifyd\Api\CaseManagementInterface;
use Magento\Signifyd\Api\CaseRepositoryInterface;
use Magento\Signifyd\Model\OrderStateService;
use Magento\Signifyd\Model\SalesOrderGrid\OrderGridUpdater;
use Magento\Signifyd\Model\SignifydGateway\Gateway;
use Magento\Signifyd\Model\SignifydGateway\GatewayException;
use Psr\Log\LoggerInterface;

/**
 * Case Creation Service
 *
 * Creates new Case entity and register it at Signifyd
 *
 * @deprecated 100.3.5 Starting from Magento 2.3.5 Signifyd core integration is deprecated in favor of
 * official Signifyd integration available on the marketplace
 */
class CreationService implements CaseCreationServiceInterface
{
    /**
     * @var CaseManagementInterface
     */
    private $caseManagement;

    /**
     * @var Gateway;
     */
    private $signifydGateway;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CaseRepositoryInterface
     */
    private $caseRepository;

    /**
     * @var OrderGridUpdater
     */
    private $orderGridUpdater;

    /**
     * @var OrderStateService
     */
    private $orderStateService;

    /**
     * CreationService constructor.
     *
     * @param CaseManagementInterface $caseManagement
     * @param Gateway $signifydGateway
     * @param LoggerInterface $logger
     * @param CaseRepositoryInterface $caseRepository
     * @param OrderGridUpdater $orderGridUpdater
     * @param OrderStateService $orderStateService
     */
    public function __construct(
        CaseManagementInterface $caseManagement,
        Gateway $signifydGateway,
        LoggerInterface $logger,
        CaseRepositoryInterface $caseRepository,
        OrderGridUpdater $orderGridUpdater,
        OrderStateService $orderStateService
    ) {
        $this->caseManagement = $caseManagement;
        $this->signifydGateway = $signifydGateway;
        $this->logger = $logger;
        $this->caseRepository = $caseRepository;
        $this->orderGridUpdater = $orderGridUpdater;
        $this->orderStateService = $orderStateService;
    }

    /**
     * {@inheritdoc}
     */
    public function createForOrder($orderId)
    {
        $case = $this->caseManagement->create($orderId);
        $this->orderGridUpdater->update($orderId);

        try {
            $caseId = $this->signifydGateway->createCase($orderId);
        } catch (GatewayException $e) {
            $this->logger->error($e->getMessage());
            return true;
        }

        $case->setCaseId($caseId);
        $this->caseRepository->save($case);
        $this->orderStateService->updateByCase($case);

        return true;
    }
}
