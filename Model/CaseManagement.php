<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model;

use Magento\Signifyd\Api\CaseManagementInterface;
use Magento\Signifyd\Api\CaseRepositoryInterface;
use Magento\Signifyd\Api\Data\CaseInterface;
use Magento\Signifyd\Api\Data\CaseInterfaceFactory;

/**
 * Implementation of case management interface
 */
class CaseManagement implements CaseManagementInterface
{
    /**
     * @var CaseRepositoryInterface
     */
    private $caseRepository;

    /**
     * @var CaseInterfaceFactory
     */
    private $caseFactory;

    /**
     * CaseManagement constructor.
     * @param CaseRepositoryInterface $caseRepository
     * @param CaseInterfaceFactory $caseFactory
     */
    public function __construct(CaseRepositoryInterface $caseRepository, CaseInterfaceFactory $caseFactory)
    {
        $this->caseRepository = $caseRepository;
        $this->caseFactory = $caseFactory;
    }

    /**
     * @inheritdoc
     */
    public function create($orderId)
    {
        $case = $this->caseFactory->create(
            ['data' => ['order_id' => $orderId, 'status' => CaseInterface::STATUS_PROCESSING]]
        );
        return $this->caseRepository->save($case);
    }

    /**
     * @inheritdoc
     */
    public function getByOrderId($orderId)
    {
        return $this->caseRepository->getById($orderId);
    }
}
