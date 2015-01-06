<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/* @var $installer \Magento\Framework\Module\DataSetup */
$installer = $this;

$data = [
    ['type_id' => 1, 'type_code' => 'hour', 'period' => 1, 'period_type' => 'HOUR'],
    ['type_id' => 2, 'type_code' => 'day', 'period' => 1, 'period_type' => 'DAY'],
];

foreach ($data as $bind) {
    $installer->getConnection()->insertForce($installer->getTable('log_summary_type'), $bind);
}
