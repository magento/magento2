<?php

$om = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$session = $om->create('Magento\Security\Model\AdminSessionInfo');
$session->setSessionId('569e2e3d752e9')
    ->setUserId(1)
    ->setStatus(\Magento\Security\Model\AdminSessionInfo::LOGGED_IN)
    ->setCreatedAt('2016-01-19 15:42:13')
    ->setUpdatedAt('2016-01-19 15:42:13')
    ->save();

$session = $om->create('Magento\Security\Model\AdminSessionInfo');
$session->setSessionId('569e2277752e9')
    ->setUserId(1)
    ->setStatus(\Magento\Security\Model\AdminSessionInfo::LOGGED_IN)
    ->setCreatedAt('2016-01-18 13:00:13')
    ->setUpdatedAt('2016-01-18 13:00:13')
    ->save();
