<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);
return [
    //this is to skip  a false positive case as this new Controller extends an existing controller to add a 404
    'app/code/Magento/HipaaImportExport/Controller/Adminhtml/Export/File/Delete.php'
];
