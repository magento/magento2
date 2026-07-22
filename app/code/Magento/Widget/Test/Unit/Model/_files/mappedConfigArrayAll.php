<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

$array1File = __DIR__ . '/mappedConfigArray1.php';
$array1 = include $array1File;
$array2File = __DIR__ . '/mappedConfigArray2.php';
$array2 = include $array2File;
return ['cms_page_link' => $array1, 'magento_giftregistry_search' => $array2];
