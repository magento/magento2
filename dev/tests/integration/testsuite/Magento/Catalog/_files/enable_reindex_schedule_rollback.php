<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/* @var \Magento\Indexer\Model\IndexerInterface $model */
$model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Indexer\Model\IndexerRegistry')
    ->get('catalogsearch_fulltext');
$model->setScheduled(false);
