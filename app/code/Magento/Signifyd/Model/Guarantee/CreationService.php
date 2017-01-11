<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\Guarantee;

use Magento\Framework\Exception\LocalizedException;
use Magento\Signifyd\Model\CaseManagement;
use Magento\Signifyd\Model\CaseUpdatingServiceFactory;
use Magento\Signifyd\Model\SignifydGateway\ApiCallException;
use Magento\Signifyd\Model\SignifydGateway\Gateway;
use Magento\Signifyd\Model\SignifydGateway\GatewayException;
use Psr\Log\LoggerInterface;

/**
 * Register guarantee at Signifyd and updates case entity
 */
class CreationService
{
    /**
     * @var CaseUpdatingServiceFactory
     */
    private $caseUpdatingServiceFactory;

    /**
     * @var Gateway
     */
    private $gateway;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CaseManagement
     */
    private $caseManagement;

    /**
     * CreationService constructor.
     *
     * @param CaseUpdatingServiceFactory $caseUpdatingServiceFactory
     * @param Gateway $gateway
     * @param CaseManagement $caseManagement
     * @param LoggerInterface $logger
     */
    public function __construct(
        CaseUpdatingServiceFactory $caseUpdatingServiceFactory,
        Gateway $gateway,
        CaseManagement $caseManagement,
        LoggerInterface $logger
    ) {
        $this->caseUpdatingServiceFactory = $caseUpdatingServiceFactory;
        $this->gateway = $gateway;
        $this->logger = $logger;
        $this->caseManagement = $caseManagement;
    }

    /**
     * Sends request to Signifyd to create guarantee for a case and updates case entity by retrieved data.
     *
     * @param int $orderId
     * @return bool
     */
    public function create($orderId)
    {
        $caseEntity = $this->caseManagement->getByOrderId($orderId);
        if ($caseEntity === null) {
            $this->logger->error("Cannot find case entity for order entity id: {$orderId}");
            return false;
        }
        $updatingService = $this->caseUpdatingServiceFactory->create('guarantees/creation');

        try {
            $disposition = $this->gateway->submitCaseForGuarantee($caseEntity->getCaseId());
            if (!$disposition) {
                $this->logger->error("Cannot retrieve guarantee disposition for case: {$caseEntity->getEntityId()}.");
                return false;
            }
            $data = [
                'caseId' => $caseEntity->getCaseId(),
                'guaranteeDisposition' => $disposition
            ];
            $updatingService->update($data);
        } catch (ApiCallException $e) {
            $this->logger->error($e->getMessage());
            return false;
        } catch (GatewayException $e) {
            $this->logger->error($e->getMessage());
            return false;
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage());
            return false;
        }

        return true;
    }
}
