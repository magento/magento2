<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/AsynchronousOperations/_files/bulk_rollback.php');
// when bulks are removed, acknowledged bulk table will be cleared too.
