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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
$om = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$mesasge = $om->create('Magento\AdminNotification\Model\Inbox');
$mesasge->setSeverity(
    \Magento\Framework\Notification\MessageInterface::SEVERITY_CRITICAL
)->setTitle(
    'Unread Critical 1'
)->save();

$mesasge = $om->create('Magento\AdminNotification\Model\Inbox');
$mesasge->setSeverity(\Magento\Framework\Notification\MessageInterface::SEVERITY_MAJOR)
    ->setTitle('Unread Major 1')
    ->save();

$mesasge = $om->create('Magento\AdminNotification\Model\Inbox');
$mesasge->setSeverity(
    \Magento\Framework\Notification\MessageInterface::SEVERITY_CRITICAL
)->setTitle(
    'Unread Critical 2'
)->save();

$mesasge = $om->create('Magento\AdminNotification\Model\Inbox');
$mesasge->setSeverity(
    \Magento\Framework\Notification\MessageInterface::SEVERITY_CRITICAL
)->setTitle(
    'Unread Critical 3'
)->save();

$mesasge = $om->create('Magento\AdminNotification\Model\Inbox');
$mesasge->setSeverity(
    \Magento\Framework\Notification\MessageInterface::SEVERITY_CRITICAL
)->setTitle(
    'Read Critical 1'
)->setIsRead(
    1
)->save();

$mesasge = $om->create('Magento\AdminNotification\Model\Inbox');
$mesasge->setSeverity(\Magento\Framework\Notification\MessageInterface::SEVERITY_MAJOR)
    ->setTitle('Unread Major 2')
    ->save();

$mesasge = $om->create('Magento\AdminNotification\Model\Inbox');
$mesasge->setSeverity(
    \Magento\Framework\Notification\MessageInterface::SEVERITY_CRITICAL
)->setTitle(
    'Removed Critical 1'
)->setIsRemove(
    1
)->save();
