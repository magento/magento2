<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model;

use Magento\Signifyd\Api\CaseCreationServiceInterface;
use Magento\Signifyd\Api\CaseManagementInterface;
use Magento\Signifyd\Model\SignifydGateway\SignifydGateway;
use Magento\Signifyd\Model\SignifydGateway\SignifydGatewayException;
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
     * @var SignifydGateway;
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
     * @param SignifydGateway $signifydGateway
     * @param LoggerInterface $logger
     */
    public function __construct(
        CaseManagementInterface $caseManagement,
        SignifydGateway $signifydGateway,
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
        } catch (SignifydGatewayException $e) {
            $this->logger->error($e->getMessage());
        }

        return true;
    }

}