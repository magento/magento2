<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var $query \Magento\Search\Model\Query */
$query = $objectManager->create(\Magento\Search\Model\Query::class);
$query->setStoreId(1);
$query->setQueryText(
    'popular_query_text'
)->setNumResults(
    1
)->setPopularity(
    100
)->setDisplayInTerms(
    1
)->setIsActive(
    1
)->setIsProcessed(
    1
)->save();
