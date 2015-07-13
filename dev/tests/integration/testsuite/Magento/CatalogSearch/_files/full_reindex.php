<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
$indexer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Indexer\Model\Indexer'
);
$indexer->load('catalogsearch_fulltext');
$indexer->reindexAll();
