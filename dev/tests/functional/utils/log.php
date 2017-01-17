<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

if (!isset($_GET['fileName'])) {
    throw new \InvalidArgumentException('Argument "fileName" must be set.');
}

$fileName = urldecode($_GET['fileName']);
$file = file_get_contents('../../../../var/log/' . $fileName);

echo serialize($file);
