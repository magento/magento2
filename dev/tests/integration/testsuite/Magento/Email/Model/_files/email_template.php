<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var \Magento\Email\Model\Template $template */
$template = $objectManager->create('Magento\Email\Model\Template');
$template->setId(1);
$template->setOptions(['area' => 'test area', 'store' => 1]);
$template->setData(
    [
        'template_text' =>
            file_get_contents(__DIR__ . '/template_fixture.html')
    ]
);
$template->setTemplateCode('fixture');
$template->save();
