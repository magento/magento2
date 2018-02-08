<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require __DIR__ . '/template.php';
require __DIR__ . '/subscribers.php';

/** @var $objectManager \Magento\TestFramework\ObjectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var $template \Magento\Newsletter\Model\Template */
$template = $objectManager->create('Magento\Newsletter\Model\Template');
$template->load('fixture_tpl', 'template_code');
$templateId = $template->getId();

$currentStore = $objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
$otherStore = $objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore('fixturestore')->getId();

/** @var $queue \Magento\Newsletter\Model\Queue */
$queue = $objectManager->create('Magento\Newsletter\Model\Queue');
$queue->setTemplateId(
    $templateId
)->setNewsletterText(
    '{{view url="images/logo.gif"}}'
)->setNewsletterSubject(
    'Subject'
)->setNewsletterSenderName(
    'CustomerSupport'
)->setNewsletterSenderEmail(
    'support@example.com'
)->setQueueStatus(
    \Magento\Newsletter\Model\Queue::STATUS_NEVER
)->setQueueStartAtByString(
    0
)->setStores(
    [$currentStore, $otherStore]
)->save();
