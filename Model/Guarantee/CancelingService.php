<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\Guarantee;

use Magento\Signifyd\Api\CaseManagementInterface;
use Magento\Signifyd\Api\GuaranteeCancelingServiceInterface;
use Magento\Signifyd\Model\CaseServices\UpdatingServiceFactory;
use Magento\Signifyd\Model\SignifydGateway\Gateway;
use Magento\Signifyd\Model\SignifydGateway\GatewayException;
use Psr\Log\LoggerInterface;

/**
 * Sends request to Signifyd to cancel guarantee and updates case entity.
 */
class CancelingService implements GuaranteeCancelingServiceInterface
{
    /**
     * @var CaseManagementInterface
     */
    private $caseManagement;

    /**
     * @var UpdatingServiceFactory
     */
    private $serviceFactory;

    /**
     * @var Gateway
     */
    private $gateway;

    /**
     * @var CancelGuaranteeAbility
     */
    private $cancelGuaranteeAbility;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * CancelingService constructor.
     *
     * @param CaseManagementInterface $caseManagement
     * @param UpdatingServiceFactory $serviceFactory
     * @param Gateway $gateway
     * @param CancelGuaranteeAbility $cancelGuaranteeAbility
     * @param LoggerInterface $logger
     */
    public function __construct(
        CaseManagementInterface $caseManagement,
        UpdatingServiceFactory $serviceFactory,
        Gateway $gateway,
        CancelGuaranteeAbility $cancelGuaranteeAbility,
        LoggerInterface $logger
    ) {
        $this->caseManagement = $caseManagement;
        $this->serviceFactory = $serviceFactory;
        $this->gateway = $gateway;
        $this->cancelGuaranteeAbility = $cancelGuaranteeAbility;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function cancelForOrder($orderId)
    {
        if (!$this->cancelGuaranteeAbility->isAvailable($orderId)) {
            return false;
        }

        $caseEntity = $this->caseManagement->getByOrderId($orderId);

        try {
            $disposition = $this->gateway->cancelGuarantee($caseEntity->getCaseId());
        } catch (GatewayException $e) {
            $this->logger->error($e->getMessage());
            return false;
        }

        $updatingService = $this->serviceFactory->create('guarantees/cancel');
        $data = [
            'guaranteeDisposition' => $disposition
        ];
        $updatingService->update($caseEntity, $data);

        return true;
    }
}
