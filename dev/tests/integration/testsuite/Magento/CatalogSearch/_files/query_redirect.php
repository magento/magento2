<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

use Magento\Framework\UrlInterface;
use Magento\Search\Model\Query;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/CatalogSearch/_files/query.php');

$objectManager = Bootstrap::getObjectManager();
/** @var UrlInterface $url */
$url = $objectManager->get(UrlInterface::class);
/** @var $query Query */
$query = $objectManager->create(Query::class);
$query->loadByQueryText('query_text');
$query->setRedirect($url->getCurrentUrl() . 'catalogsearch/result/?q=query_text&cat=41')
    ->save();
