<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */

use Magento\TestFramework\Helper\Bootstrap;
use Magento\UrlRewrite\Model\UrlRewrite;

$objectManager = Bootstrap::getObjectManager();

/** @var UrlRewrite $urlRewrite */
$urlRewrite = $objectManager->create(UrlRewrite::class);
$urlRewrite->load('non-exist-entity.html', 'request_path');
$urlRewrite->delete();
