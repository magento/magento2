<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture(
    'Magento/Backend/controllers/_files/cache/empty_storage.php'
);
