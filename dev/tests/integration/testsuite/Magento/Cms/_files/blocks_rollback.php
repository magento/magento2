<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Helper\Bootstrap;

/** @var BlockRepositoryInterface $blockRepository */
$blockRepository = Bootstrap::getObjectManager()->get(BlockRepositoryInterface::class);

foreach (['enabled_block', 'disabled_block'] as $blockId) {
    try {
        $blockRepository->deleteById($blockId);
    } catch (NoSuchEntityException $e) {
        /**
         * Tests which are wrapped with MySQL transaction clear all data by transaction rollback.
         */
    }
}
