<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

use Magento\Indexer\Model\Processor;
use Magento\TestFramework\Helper\Bootstrap;

/** @var Processor $processor */
$processor = Bootstrap::getObjectManager()->get(Processor::class);
$processor->reindexAllInvalid();
