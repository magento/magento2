<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/** @var $this \Magento\Cms\Model\Resource\Setup */
$installer = $this->createMigrationSetup();
$installer->startSetup();

$installer->appendClassAliasReplace(
    'cms_block',
    'content',
    \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_BLOCK,
    \Magento\Framework\Module\Setup\Migration::FIELD_CONTENT_TYPE_WIKI,
    ['block_id']
);
$installer->appendClassAliasReplace(
    'cms_page',
    'content',
    \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_BLOCK,
    \Magento\Framework\Module\Setup\Migration::FIELD_CONTENT_TYPE_WIKI,
    ['page_id']
);
$installer->appendClassAliasReplace(
    'cms_page',
    'layout_update_xml',
    \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_BLOCK,
    \Magento\Framework\Module\Setup\Migration::FIELD_CONTENT_TYPE_XML,
    ['page_id']
);
$installer->appendClassAliasReplace(
    'cms_page',
    'custom_layout_update_xml',
    \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_BLOCK,
    \Magento\Framework\Module\Setup\Migration::FIELD_CONTENT_TYPE_XML,
    ['page_id']
);

$installer->doUpdateClassAliases();

$installer->endSetup();
