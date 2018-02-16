<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
$block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Cms\Model\Page');
$block->load('no-route', 'identifier');
$block->setIsActive(0)->save();
