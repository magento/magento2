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

/** @var $installer \Magento\Framework\Module\Setup\Migration */
$installer = $this->createMigrationSetup();
$installer->startSetup();

$attributeData = $this->getAttribute('catalog_category', 'custom_layout_update');
$installer->appendClassAliasReplace(
    'catalog_category_entity_text',
    'value',
    \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_BLOCK,
    \Magento\Framework\Module\Setup\Migration::FIELD_CONTENT_TYPE_XML,
    array('value_id'),
    'attribute_id = ' . (int)$attributeData['attribute_id']
);

$attributeData = $this->getAttribute('catalog_product', 'custom_layout_update');
$installer->appendClassAliasReplace(
    'catalog_product_entity_text',
    'value',
    \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_BLOCK,
    \Magento\Framework\Module\Setup\Migration::FIELD_CONTENT_TYPE_XML,
    array('value_id'),
    'attribute_id = ' . (int)$attributeData['attribute_id']
);

$installer->appendClassAliasReplace(
    'catalog_eav_attribute',
    'frontend_input_renderer',
    \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_BLOCK,
    \Magento\Framework\Module\Setup\Migration::FIELD_CONTENT_TYPE_PLAIN,
    array('attribute_id')
);
$installer->doUpdateClassAliases();

$installer->endSetup();
