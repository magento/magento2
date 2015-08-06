<?php
/**
 * Classes that are restricted to use directly.
 * A <replacement> will be suggested to be used instead.
 * Use <whitelist> to specify files and directories that are allowed to use restricted classes.
 *
 * Format: array(<class_name>, <replacement>[, array(<whitelist>)]])
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    [
        'Zend_Db_Select',
        '\Magento\Framework\DB\Select',
        [
            '/lib/internal/Magento/Framework/DB/Select.php',
            '/lib/internal/Magento/Framework/DB/Adapter/Pdo/Mysql.php',
            '/lib/internal/Magento/Framework/Model/Resource/Iterator.php',
        ]
    ],
    [
        'Zend_Db_Adapter_Pdo_Mysql',
        '\Magento\Framework\DB\Adapter\Pdo\Mysql',
        [
            '/lib/internal/Magento/Framework/DB/Adapter/Pdo/Mysql.php',
        ]
    ],
];
