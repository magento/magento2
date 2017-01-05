<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model;

use Magento\Signifyd\Api\CaseCreationServiceInterface;
use Magento\Signifyd\Api\CaseManagementInterface;
use Magento\Signifyd\Model\SignifydGateway\ApiCallException;
use Magento\Signifyd\Model\SignifydGateway\Gateway;
use Magento\Signifyd\Model\SignifydGateway\GatewayException;
use Psr\Log\LoggerInterface;

/**
 * Case Creation Service
 *
 * Creates new Case entity and register it at Signifyd
 */
class CaseCreationService implements CaseCreationServiceInterface
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
     * CaseCreationService constructor.
     *
     * @param CaseManagementInterface $caseManagement
     * @param Gateway $signifydGateway
     * @param LoggerInterface $logger
     */
    public function __construct(
        CaseManagementInterface $caseManagement,
        Gateway $signifydGateway,
        LoggerInterface $logger
    ) {
        $this->caseManagement = $caseManagement;
        $this->signifydGateway = $signifydGateway;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function createForOrder($orderId)
    {
        $this->caseManagement->create($orderId);

        try {
            $this->signifydGateway->createCase($orderId);
        } catch (ApiCallException $e) {
            $this->logger->error($e->getMessage());
        } catch (GatewayException $e) {
            $this->logger->error($e->getMessage());
        }

        return true;
    }
}
