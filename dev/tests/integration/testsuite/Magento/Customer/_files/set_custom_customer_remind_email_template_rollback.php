<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\App\Config\MutableScopeConfigInterface;

require __DIR__ . '/../../../Magento/Email/_files/customer_password_email_template_rollback.php';

/** @var MutableScopeConfigInterface $mutableScopeConfig */
$mutableScopeConfig = $objectManager->get(MutableScopeConfigInterface::class);

$mutableScopeConfig->clean();
