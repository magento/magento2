<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Sales/_files/order_rollback.php');
Resolver::getInstance()->requireDataFixture(
    'Magento/Customer/_files/customer_for_second_website_with_address_rollback.php'
);
