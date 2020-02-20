<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\Data\BlockInterface;
use Magento\Cms\Api\Data\BlockInterfaceFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

/** @var BlockRepositoryInterface $blockRepository */
$blockRepository = Bootstrap::getObjectManager()->get(BlockRepositoryInterface::class);
/** @var BlockInterfaceFactory $blockFactory */
$blockFactory = Bootstrap::getObjectManager()->get(BlockInterfaceFactory::class);
$storeId = Bootstrap::getObjectManager()->get(StoreManagerInterface::class)->getStore()->getId();

/** @var BlockInterface $block */
$block = $blockFactory->create([
    'data' => [
        BlockInterface::IDENTIFIER => 'enabled_block',
        BlockInterface::TITLE => 'Enabled CMS Block Title',
        BlockInterface::CONTENT => '
            <h1>Enabled Block</h1>
            <a href="{{store url=""}}">store url</a>
            <p>Config value: "{{config path="web/unsecure/base_url"}}".</p>
            <p>Custom variable: "{{customvar code="variable_code"}}".</p>
            ',
        BlockInterface::IS_ACTIVE => 1,
        'store_id' => [$storeId],
    ]
]);
$blockRepository->save($block);

/** @var BlockInterface $block */
$block = $blockFactory->create([
    'data' => [
        BlockInterface::IDENTIFIER => 'disabled_block',
        BlockInterface::TITLE => 'Disabled CMS Block Title',
        BlockInterface::CONTENT => '
            <h1>Disabled Block</h1>
            <a href="{{store url=""}}">store url</a>
            <p>Config value: "{{config path="web/unsecure/base_url"}}".</p>
            <p>Custom variable: "{{customvar code="variable_code"}}".</p>
            ',
        BlockInterface::IS_ACTIVE => 0,
        'store_id' => [$storeId],
    ]
]);
$blockRepository->save($block);
