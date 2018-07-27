<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require_once 'pre-install/CliColors.php';
require_once 'pre-install/PreInstallCheck.php';

$cliColors = new CliColors();
$preCheck = new PreInstallCheck($cliColors);

$preCheck->run();
