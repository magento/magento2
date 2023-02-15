<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\TestFramework\Helper\Bootstrap;
use Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor;

$ruleProductProcessor = Bootstrap::getObjectManager()->get(RuleProductProcessor::class);
$indexer = $ruleProductProcessor->getIndexer();
$indexer->reindexAll();
$indexer->setScheduled(true);
