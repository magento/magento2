<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Framework\UrlInterface;
use Magento\TestFramework\Helper\Bootstrap;

include 'query.php';

$objectManager = Bootstrap::getObjectManager();
/** @var UrlInterface $url */
$url = $objectManager->get(UrlInterface::class);

$query->setRedirect($url->getCurrentUrl() . 'catalogsearch/result/?q=query_text&cat=41')
    ->save();
