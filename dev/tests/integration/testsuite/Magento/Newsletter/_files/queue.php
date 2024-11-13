<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Newsletter/_files/template.php');
Resolver::getInstance()->requireDataFixture('Magento/Newsletter/_files/subscribers.php');

/** @var $objectManager \Magento\TestFramework\ObjectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var $template \Magento\Newsletter\Model\Template */
$template = $objectManager->create(\Magento\Newsletter\Model\Template::class);
$template->load('fixture_tpl', 'template_code');
$templateId = $template->getId();

$currentStore = $objectManager->get(\Magento\Store\Model\StoreManagerInterface::class)->getStore()->getId();
$otherStore = $objectManager->get(\Magento\Store\Model\StoreManagerInterface::class)->getStore('fixturestore')->getId();

/** @var $queue \Magento\Newsletter\Model\Queue */
$queue = $objectManager->create(\Magento\Newsletter\Model\Queue::class);
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
    null
)->setStores(
    [$currentStore, $otherStore]
)->save();
