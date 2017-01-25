<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$config = $objectManager->get(\Magento\Config\Model\Config::class);
$config->setDataByPath('payment/' . \Magento\Authorizenet\Model\Directpost::METHOD_CODE . '/active', 1);
$config->save();
$config->setDataByPath('payment/' . \Magento\Authorizenet\Model\Directpost::METHOD_CODE . '/login', 'mylogin');
$config->save();
$config->setDataByPath('payment/' . \Magento\Authorizenet\Model\Directpost::METHOD_CODE . '/trans_md5', 'md5hash');
$config->save();
