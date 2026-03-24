<?php
/**
 * Entry point for static resources (JS, CSS, etc.)
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

require realpath(__DIR__) . '/../app/bootstrap.php';
$bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $_SERVER);
/** @var \Magento\Framework\App\StaticResource $app */
$app = $bootstrap->createApplication(\Magento\Framework\App\StaticResource::class);
$bootstrap->run($app);
