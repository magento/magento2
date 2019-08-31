<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
require __DIR__ . '/configurable_products_rollback.php';

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

require __DIR__ . '/configurable_attribute_2_rollback.php';

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
