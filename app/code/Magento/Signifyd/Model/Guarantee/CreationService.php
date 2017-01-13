<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\Guarantee;

use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Signifyd\Api\CaseManagementInterface;
use Magento\Signifyd\Api\GuaranteeCreationServiceInterface;
use Magento\Signifyd\Model\CaseServices\UpdatingServiceFactory;
use Magento\Signifyd\Model\SignifydGateway\Gateway;
use Magento\Signifyd\Model\SignifydGateway\GatewayException;
use Psr\Log\LoggerInterface;

/**
 * Register guarantee at Signifyd and updates case entity
 */
class CreationService implements GuaranteeCreationServiceInterface
{
    /**
     * @var CaseManagementInterface
     */
    private $caseManagement;

    /**
     * @var UpdatingServiceFactory
     */
    private $caseUpdatingServiceFactory;

    /**
     * @var Gateway
     */
    private $gateway;

    /**
     * @var CreateGuaranteeAbility
     */
    private $createGuaranteeAbility;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * CreationService constructor.
     *
     * @param CaseManagementInterface $caseManagement
     * @param UpdatingServiceFactory $caseUpdatingServiceFactory
     * @param Gateway $gateway
     * @param CreateGuaranteeAbility $createGuaranteeAbility
     * @param LoggerInterface $logger
     */
    public function __construct(
        CaseManagementInterface $caseManagement,
        UpdatingServiceFactory $caseUpdatingServiceFactory,
        Gateway $gateway,
        CreateGuaranteeAbility $createGuaranteeAbility,
        LoggerInterface $logger
    ) {
        $this->caseManagement = $caseManagement;
        $this->caseUpdatingServiceFactory = $caseUpdatingServiceFactory;
        $this->gateway = $gateway;
        $this->createGuaranteeAbility = $createGuaranteeAbility;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function createForOrder($orderId)
    {
        if (!$this->createGuaranteeAbility->isAvailable($orderId)) {
            return false;
        }

        $caseEntity = $this->caseManagement->getByOrderId($orderId);

        try {
            $disposition = $this->gateway->submitCaseForGuarantee($caseEntity->getCaseId());
        } catch (GatewayException $e) {
            $this->logger->error($e->getMessage());
            return false;
        }

        $updatingService = $this->caseUpdatingServiceFactory->create('guarantees/creation');
        $data = [
            'caseId' => $caseEntity->getCaseId(),
            'guaranteeDisposition' => $disposition
        ];
        $updatingService->update($caseEntity, $data);

        return true;
    }
}
