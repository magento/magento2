<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Downloadable/_files/product_downloadable_rollback.php');
Resolver::getInstance()->requireDataFixture('Magento/Sales/_files/default_rollback.php');
