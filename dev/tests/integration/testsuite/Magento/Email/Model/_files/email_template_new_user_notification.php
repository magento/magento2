<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var \Magento\Email\Model\Template $template */
$template = $objectManager->create(\Magento\Email\Model\Template::class);
$template->setOptions(['area' => 'test area', 'store' => 1]);
$template->setData(
    [
        'template_text' => 'New User Notification Custom Text',
        'template_code' => 'New User Notification Custom Code',
        'template_type' => \Magento\Email\Model\Template::TYPE_TEXT,
        'orig_template_code' => 'admin_emails_new_user_notification_template'
    ]
);
$template->save();
