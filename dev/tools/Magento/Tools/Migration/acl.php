<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

require_once './Acl/Formatter.php';
require_once './Acl/FileManager.php';
require_once './Acl/Generator.php';

$shortOpts = 'ph';
$options = getopt($shortOpts);
try {
    $tool = new \Magento\Tools\Migration\Acl\Generator(
        new \Magento\Tools\Migration\Acl\Formatter(),
        new \Magento\Tools\Migration\Acl\FileManager(),
        $options
    );
    $tool->run();
} catch (\Exception $exp) {
    echo $exp->getMessage();
}
