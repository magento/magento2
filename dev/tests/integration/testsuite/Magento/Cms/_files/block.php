<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var $block \Magento\Cms\Model\Block */
$block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Cms\Model\Block::class);
$block->setTitle(
    'CMS Block Title'
)->setIdentifier(
    'fixture_block'
)->setContent(
    '<h1>Fixture Block Title</h1>
<a href="{{store url=""}}">store url</a>
<p>Config value: "{{config path="web/unsecure/base_url"}}".</p>
<p>Custom variable: "{{customvar code="variable_code"}}".</p>
'
)->setIsActive(
    1
)->setStores(
    [
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Store\Model\StoreManagerInterface::class
        )->getStore()->getId()
    ]
)->save();
