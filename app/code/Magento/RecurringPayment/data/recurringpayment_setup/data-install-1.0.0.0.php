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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/** @var $this \Magento\Catalog\Model\Resource\Setup */
$this->installEntities();
$entityTypeId = $this->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
$attributeSetId = $this->getAttributeSetId($entityTypeId, 'Default');

$groupName = 'Recurring Payment';
$this->updateAttributeGroup($entityTypeId, $attributeSetId, $groupName, 'sort_order', 41);
$this->updateAttributeGroup($entityTypeId, $attributeSetId, $groupName, 'attribute_group_code', 'recurring-payment');
$this->updateAttributeGroup($entityTypeId, $attributeSetId, $groupName, 'tab_group_code', 'advanced');

$this->addAttributeToGroup($entityTypeId, $attributeSetId, $groupName, 'is_recurring');
$this->addAttributeToGroup($entityTypeId, $attributeSetId, $groupName, 'recurring_payment');

$connection = $this->getConnection();
$adminRuleTable = $this->getTable('authorization_rule');
$connection->update(
    $adminRuleTable,
    array('resource_id' => 'Magento_RecurringPayment::recurring_payment'),
    array('resource_id = ?' => 'Magento_Sales::recurring_payment')
);
