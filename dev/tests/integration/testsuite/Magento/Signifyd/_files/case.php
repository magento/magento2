<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\Signifyd\Api\CaseRepositoryInterface;
use Magento\Signifyd\Api\Data\CaseInterface;
use Magento\Signifyd\Api\Data\CaseInterfaceFactory;

require __DIR__ . '/order.php';

/** @var CaseInterfaceFactory $caseFactory */
$caseFactory = $objectManager->get(CaseInterfaceFactory::class);

/** @var CaseInterface $case */
$case = $caseFactory->create();
$case->setCaseId(123)
    ->setGuaranteeEligible(true)
    ->setGuaranteeDisposition(CaseInterface::GUARANTEE_PENDING)
    ->setStatus(CaseInterface::STATUS_PROCESSING)
    ->setScore(553)
    ->setOrderId($order->getEntityId())
    ->setAssociatedTeam(124)
    ->setReviewDisposition(CaseInterface::DISPOSITION_GOOD)
    ->setCreatedAt('2016-12-12T15:17:17+0000')
    ->setUpdatedAt('2016-12-12T19:23:16+0000');

/** @var CaseRepositoryInterface $caseRepository */
$caseRepository = $objectManager->get(CaseRepositoryInterface::class);
$caseRepository->save($case);
