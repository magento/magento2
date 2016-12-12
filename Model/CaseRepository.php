<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model;

use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Signifyd\Api\CaseRepositoryInterface;
use Magento\Signifyd\Api\Data\CaseInterface;
use Magento\Signifyd\Api\Data\CaseInterfaceFactory;

/**
 * Repository for Case interface
 */
class CaseRepository implements CaseRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var CaseInterfaceFactory
     */
    private $caseFactory;

    /**
     * CaseRepository constructor.
     * @param EntityManager $entityManager
     * @param CaseInterfaceFactory $caseFactory
     */
    public function __construct(EntityManager $entityManager, CaseInterfaceFactory $caseFactory)
    {
        $this->entityManager = $entityManager;
        $this->caseFactory = $caseFactory;
    }

    /**
     * @inheritdoc
     */
    public function save(CaseInterface $case)
    {
        return $this->entityManager->save($case);
    }

    /**
     * @inheritdoc
     */
    public function getById($orderId)
    {
        $case = $this->caseFactory->create();
        return $this->entityManager->load($case, $orderId);
    }
}
