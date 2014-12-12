<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
/** @var \Magento\Newsletter\Model\Template $template */
$template = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Newsletter\Model\Template');

$templateData = [
    'template_code' => 'some_unique_code',
    'template_type' => Magento\Newsletter\Model\Template::TYPE_TEXT,
    'subject' => 'test data2__22',
    'template_sender_email' => 'sender@email.com',
    'template_sender_name' => 'Test Sender Name 222',
    'text' => 'Template Content 222',
];
$template->setData($templateData);
$template->save();
