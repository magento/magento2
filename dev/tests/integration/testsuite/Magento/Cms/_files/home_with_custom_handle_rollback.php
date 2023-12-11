<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Cms\Model\Page\CustomLayout\CustomLayoutRepository;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var PageRepositoryInterface $pageRepository */
$pageRepository = $objectManager->get(PageRepositoryInterface::class);
$cmsPage = $pageRepository->getById('home');
$cmsPageId = (int)$cmsPage->getId();

/** @var CustomLayoutRepository $customLayoutRepository */
$customLayoutRepository = $objectManager->get(CustomLayoutRepository::class);
$customLayoutRepository->deleteFor($cmsPageId);
