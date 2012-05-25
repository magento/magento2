<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Mage
 * @package    Mage
 * @copyright  Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

define('SYNOPSIS', <<<SYNOPSIS
php -f install.php -- --show_locales
php -f install.php -- --show_currencies
php -f install.php -- --show_timezones
php -f install.php -- --show_install_options
php -f install.php -- [--<install_option_name> "<option_value>", ...]
php -f install.php -- --uninstall

SYNOPSIS
);

/**
 * Parse command line arguments
 */
$currentArgName = false;
$args = array();
foreach ($_SERVER['argv'] as $argNameOrValue) {
    if (substr($argNameOrValue, 0, 2) == '--') {
        // argument name
        $currentArgName = substr($argNameOrValue, 2);
        // in case if argument doesn't need a value
        $args[$currentArgName] = true;
    } else {
        // argument value
        if ($currentArgName) {
            $args[$currentArgName] = $argNameOrValue;
        }
        $currentArgName = false;
    }
}

if (empty($args)) {
    echo SYNOPSIS;
    exit(1);
}

require_once __DIR__ . '/../../app/bootstrap.php';

$installer = new Mage_Install_Model_Installer_Console();
if (isset($args['show_locales'])) {
    var_export($installer->getAvailableLocales());
} else if (isset($args['show_currencies'])) {
    var_export($installer->getAvailableCurrencies());
} else if (isset($args['show_timezones'])) {
    var_export($installer->getAvailableTimezones());
} else if (isset($args['show_install_options'])) {
    var_export($installer->getAvailableInstallOptions());
} else {
    $isUninstallMode = isset($args['uninstall']);
    if ($isUninstallMode) {
        $result = $installer->uninstall();
    } else {
        $result = $installer->install($args);
    }
    if (!$installer->hasErrors()) {
        if ($isUninstallMode) {
            $msg = $result ? 'Uninstalled successfully' : 'Ignoring attempt to uninstall non-installed application';
        } else {
            $msg = 'Installed successfully' . ($result ? ' (encryption key "' . $result . '")' : '');
        }
        echo $msg . PHP_EOL;
    } else {
        echo implode(PHP_EOL, $installer->getErrors()) . PHP_EOL;
        exit(1);
    }
}
