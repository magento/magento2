<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/** @var $installer \Magento\GiftMessage\Model\Resource\Setup */
$installer = $this;
/**
 * Add 'gift_message_id' attributes for entities
 */

$options = ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 'visible' => false, 'required' => false];
$entities = ['quote', 'quote_address', 'quote_item', 'quote_address_item'];
foreach ($entities as $entity) {
    $installer->createQuoteSetup(
        ['resourceName' => 'quote_setup']
    )->addAttribute($entity, 'gift_message_id', $options);
}

$salesSetup = $installer->createSalesSetup(['resourceName' => 'sales_setup']);
$salesSetup->addAttribute('order', 'gift_message_id', $options);
$salesSetup->addAttribute('order_item', 'gift_message_id', $options);
/**
 * Add 'gift_message_available' attributes for entities
 */
$salesSetup->addAttribute('order_item', 'gift_message_available', $options);

$catalogSetup = $installer->createCatalogSetup(['resourceName' => 'catalog_setup']);
$catalogSetup->addAttribute(
    \Magento\Catalog\Model\Product::ENTITY,
    'gift_message_available',
    [
        'group' => 'Gift Options',
        'backend' => 'Magento\Catalog\Model\Product\Attribute\Backend\Boolean',
        'frontend' => '',
        'label' => 'Allow Gift Message',
        'input' => 'select',
        'class' => '',
        'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
        'global' => true,
        'visible' => true,
        'required' => false,
        'user_defined' => false,
        'default' => '',
        'apply_to' => '',
        'input_renderer' => 'Magento\GiftMessage\Block\Adminhtml\Product\Helper\Form\Config',
        'visible_on_front' => false
    ]
);
/** @var $this \Magento\GiftMessage\Model\Resource\Setup */

$groupName = 'Autosettings';
$entityTypeId = $catalogSetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
$attributeSetId = $catalogSetup->getAttributeSetId($entityTypeId, 'Default');

$attribute = $catalogSetup->getAttribute($entityTypeId, 'gift_message_available');
if ($attribute) {
    $catalogSetup->addAttributeToGroup($entityTypeId, $attributeSetId, $groupName, $attribute['attribute_id'], 60);
}

if (!$catalogSetup->getAttributesNumberInGroup($entityTypeId, $attributeSetId, 'Gift Options')) {
    $catalogSetup->removeAttributeGroup($entityTypeId, $attributeSetId, 'Gift Options');
}
