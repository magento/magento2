<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

// when bulks are removed, acknowledged bulk table will be cleared too.';
Resolver::getInstance()->requireDataFixture('Magento/AsynchronousOperations/_files/bulk_rollback.php');
