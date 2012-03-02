<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Mage_Newsletter
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require __DIR__ . '/template.php';
require __DIR__ . '/subscribers.php';

$template = new Mage_Newsletter_Model_Template;
$template->load('fixture_tpl', 'template_code');
$templateId = $template->getId();

$currentStore = Mage::app()->getStore()->getId();
$otherStore = Mage::app()->getStore('fixturestore')->getId();

$queue = new Mage_Newsletter_Model_Queue;
$queue->setTemplateId($templateId)
    ->setNewsletterText('{{skin url="images/logo.gif"}}')
    ->setNewsletterSubject('Subject')
    ->setNewsletterSenderName('CustomerSupport')
    ->setNewsletterSenderEmail('support@example.com')
    ->setQueueStatus(Mage_Newsletter_Model_Queue::STATUS_NEVER)
    ->setQueueStartAtByString(0)
    ->setStores(array($currentStore, $otherStore))
    ->save()
;
