<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
$indexer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Indexer\Model\Indexer::class
);
$indexer->load('catalogsearch_fulltext');
$indexer->reindexAll();
