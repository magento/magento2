<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
 * @since 2.2.0
 */
class CancelingService implements GuaranteeCancelingServiceInterface
{
    /**
     * @var CaseManagementInterface
     * @since 2.2.0
     */
    private $caseManagement;

    /**
     * @var UpdatingServiceFactory
     * @since 2.2.0
     */
    private $serviceFactory;

    /**
     * @var Gateway
     * @since 2.2.0
     */
    private $gateway;

    /**
     * @var CancelGuaranteeAbility
     * @since 2.2.0
     */
    private $cancelGuaranteeAbility;

    /**
     * @var LoggerInterface
     * @since 2.2.0
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
     * @since 2.2.0
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
     * @since 2.2.0
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
