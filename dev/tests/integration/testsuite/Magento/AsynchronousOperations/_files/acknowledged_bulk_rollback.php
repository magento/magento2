<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/AsynchronousOperations/_files/bulk_rollback.php');
// when bulks are removed, acknowledged bulk table will be cleared too.
