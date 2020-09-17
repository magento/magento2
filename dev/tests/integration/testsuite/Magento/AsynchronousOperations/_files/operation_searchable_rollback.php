<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

// when bulks are removed, acknowledged bulk table will be cleared too.';
Resolver::getInstance()->requireDataFixture('Magento/AsynchronousOperations/_files/bulk_rollback.php');
