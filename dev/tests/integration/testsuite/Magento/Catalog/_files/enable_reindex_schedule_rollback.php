<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

/* @var \Magento\Framework\Indexer\IndexerInterface $model */
$model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
    \Magento\Framework\Indexer\IndexerRegistry::class
)->get('catalogsearch_fulltext');
$model->setScheduled(false);
