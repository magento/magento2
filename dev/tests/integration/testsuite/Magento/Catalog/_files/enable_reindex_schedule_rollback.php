<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* @var \Magento\Framework\Indexer\IndexerInterface $model */
$model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\Indexer\IndexerRegistry')
    ->get('catalogsearch_fulltext');
$model->setScheduled(false);
