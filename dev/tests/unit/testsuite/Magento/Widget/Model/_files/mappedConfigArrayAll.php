<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
$array1File = __DIR__ . '/mappedConfigArray1.php';
$array1 = include $array1File;
$array2File = __DIR__ . '/mappedConfigArray2.php';
$array2 = include $array2File;
return ['cms_page_link' => $array1, 'magento_giftregistry_search' => $array2];
