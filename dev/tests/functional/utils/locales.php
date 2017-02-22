<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$type = isset($_GET['type']) ? $_GET['type'] : 'all';

switch ($type) {
    case 'deployed':
        $directory = __DIR__ . '/../../../../pub/static/adminhtml/Magento/backend';
        $localesDirs = array_diff(scandir($directory), ['..', '.']);
        echo implode('|', $localesDirs);
        break;
    case 'all':

        break;
}
