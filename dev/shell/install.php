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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

use Magento\Framework\App\State as AppState;
use Magento\Framework\App\Bootstrap;

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
    $detailedOptions = array('show_locales', 'show_currencies', 'show_timezones', 'show_install_options');
    echo 'Detailed info:' . PHP_EOL;
    foreach ($detailedOptions as $option) {
        echo '  php -f ' . $_SERVER['argv'][0] . ' -- --' . $option . PHP_EOL;
    }
    echo "php -f {$_SERVER['argv'][0]} -- [--<install_option_name> \"<option_value>\" ...]\n";

    $exampleOptions = array(
        'license_agreement_accepted' => 'yes',
        'locale' => 'en_US',
        'timezone' => '"America/Los_Angeles"',
        'default_currency' => 'USD',
        'db_host' => 'localhost',
        'db_name' => 'magento',
        'db_user' => 'root',
        'url' => '"http://magento.local/"',
        'use_rewrites' => 'no',
        'use_secure_admin' => 'yes',
        'admin_lastname' => 'Smith',
        'admin_firstname' => 'John',
        'admin_email' => '"john.smith@some-email.com"',
        'admin_username' => 'admin',
        'admin_password' => '1234qasd',
        'use_secure' => 'no',
        'secure_base_url' => '"https://magento.local"',
        'cleanup_database' => '',
        'bootstrap' => '{"extra":{"key":"value"}}',
    );
    echo 'Example of installation:' . PHP_EOL;
    echo '  php -f ' . $_SERVER['argv'][0] . ' --';
    foreach ($exampleOptions as $option => $value) {
        if (!empty($value)) {
            echo ' --' . $option . ' ' . $value;
        } else {
            echo ' --' . $option;
        }
    }
    echo PHP_EOL;
    exit(1);
}

require __DIR__ . '/../../app/bootstrap.php';

$params = $_SERVER;
$params[Bootstrap::PARAM_REQUIRE_IS_INSTALLED] = false;
if (!isset($params[AppState::PARAM_MODE])) {
    $params[AppState::PARAM_MODE] = AppState::MODE_DEVELOPER;
}
if (isset($args['bootstrap'])) {
    $extra = json_decode($args['bootstrap'], true);
    if (!is_array($extra)) {
        throw new \Exception("Unable to decode JSON in the parameter 'bootstrap'");
    }
    $params = array_replace_recursive($params, $extra);
}
$bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $params);
/** @var \Magento\Install\App\Console $app */
$app = $bootstrap->createApplication('Magento\Install\App\Console', ['arguments' => $args]);
$bootstrap->run($app);
