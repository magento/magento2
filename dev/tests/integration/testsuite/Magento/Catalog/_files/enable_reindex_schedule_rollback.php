<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* @var \Magento\Framework\Indexer\IndexerInterface $model */
$model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\Indexer\IndexerRegistry')
    ->get('catalogsearch_fulltext');
$model->setScheduled(false);
