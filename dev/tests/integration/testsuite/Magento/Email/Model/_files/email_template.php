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
        'template_text' => file_get_contents(__DIR__ . '/template_fixture.html'),
        'template_code' => \Magento\Theme\Model\Config\ValidatorTest::TEMPLATE_CODE,
        'template_type' => \Magento\Email\Model\Template::TYPE_TEXT,
        'orig_template_code' => 'template_fixture'
    ]
);
$template->save();
