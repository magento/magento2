<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/* @var \Magento\Framework\Indexer\IndexerInterface $model */
$model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\Indexer\IndexerRegistry')
    ->get('catalogsearch_fulltext');
$model->setScheduled(true);
