<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Customer\Model\EmailNotification;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\MutableScopeConfigInterface;

require __DIR__ . '/../../../Magento/Email/_files/customer_password_email_template.php';

/** @var MutableScopeConfigInterface $mutableScopeConfig */
$mutableScopeConfig = $objectManager->get(MutableScopeConfigInterface::class);

$mutableScopeConfig->setValue(
    EmailNotification::XML_PATH_FORGOT_EMAIL_TEMPLATE,
    $template->getId(),
    ScopeInterface::SCOPE_STORE,
    $store->getCode()
);
