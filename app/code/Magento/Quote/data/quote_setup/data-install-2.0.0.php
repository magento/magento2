<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var $this \Magento\Quote\Model\Resource\Setup */

/**
 * Install eav entity types to the eav/entity_type table
 */

$attributes = [
    'vat_id' => ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT],
    'vat_is_valid' => ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT],
    'vat_request_id' => ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT],
    'vat_request_date' => ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT],
    'vat_request_success' => ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT],
];

foreach ($attributes as $attributeCode => $attributeParams) {
    $this->addAttribute('quote_address', $attributeCode, $attributeParams);
}
