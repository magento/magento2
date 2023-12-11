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
/** @var StoreManagerInterface $stores */
$stores = Bootstrap::getObjectManager()->get(StoreManagerInterface::class)->getStores();
array_shift($stores);

/** @var BlockInterface $block */
$block = $blockFactory->create([
    'data' => [
        BlockInterface::IDENTIFIER => 'test-block',
        BlockInterface::TITLE => 'Second store block',
        BlockInterface::CONTENT => '
            <h1>Test Block 1 for Second Store</h1>
            <a href="{{store url=""}}">store url</a>
            <p>Config value: "{{config path="trans_email/ident_general/name"}}".</p>
            <p>Custom path: "{{config path="trans_email/ident_general/email"}}".</p>
            ',
        BlockInterface::IS_ACTIVE => 1,
        'store_id' => [$stores[0]->getId()],
    ]
]);
$blockRepository->save($block);

/** @var BlockInterface $block */
$block = $blockFactory->create([
    'data' => [
        BlockInterface::IDENTIFIER => 'test-block-2',
        BlockInterface::TITLE => 'Second store block 2',
        BlockInterface::CONTENT => '
            <h1>Test Block 2 for Second Store</h1>
            <a href="{{store url=""}}">store url</a>
            <p>Config value: "{{config path="trans_email/ident_general/name"}}".</p>
            <p>Custom path: "{{config path="trans_email/ident_general/email"}}".</p>
            ',
        BlockInterface::IS_ACTIVE => 1,
        'store_id' => [$stores[0]->getId()],
    ]
]);
$blockRepository->save($block);

/** @var BlockInterface $block */
$block = $blockFactory->create([
    'data' => [
        BlockInterface::IDENTIFIER => 'test-block',
        BlockInterface::TITLE => 'Third store block',
        BlockInterface::CONTENT => '
            <h1>Test Block for Third Store</h1>
            <a href="{{store url=""}}">store url</a>
            <p>Config value: "{{config path="trans_email/ident_general/name"}}".</p>
            <p>Custom path: "{{config path="trans_email/ident_general/email"}}".</p>
            ',
        BlockInterface::IS_ACTIVE => 1,
        'store_id' => [$stores[1]->getId()],
    ]
]);
$blockRepository->save($block);
