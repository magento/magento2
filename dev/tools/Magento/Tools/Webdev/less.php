<?php
/**
 * A script for deploying static view files for Magento system "production mode"
 *
 * The resulting files will be recorded into pub/static directory.
 * They can be used not only by the server where Magento instance is,
 * but also can be copied to a CDN, and the Magento instance may be configured to generate base URL to the CDN.
 *
 * {license_notice}
 *
 * @copyright  {copyright}
 * @license    {license_link}
 */
use Magento\Framework\Autoload\AutoloaderRegistry;

$baseName = basename(__FILE__);
$options = getopt('', array('locale::', 'area::', 'theme::', 'files::', 'verbose::', 'help::', 'setup::'));
define('USAGE', "USAGE:\n\tphp -f {$baseName} -- [--locale=en_US] [--area=frontend|adminhtml|doc]  [--theme=Vendor/theme] [--files=css/styles,css/styles2,..] [--verbose=0|1] [--setup] [--help]\n");
require __DIR__ . '/../../../../../app/bootstrap.php';

AutoloaderRegistry::getAutoloader()->addPsr4(
    'Magento\\',
    [BP . '/dev/tests/static/framework/Magento/', realpath(__DIR__ . '/../../../Magento/')]
);

// parse all options
if (isset($options['help'])) {
    echo USAGE;
    exit(0);
}
if (isset($options['setup'])) {
    echo "Setting up... \n";
    if (!file_exists(BP . '/Gruntfile.js')) {
        copy(BP . '/dev/tools/Magento/Tools/Webdev/Gruntfile.js.example', BP . '/Gruntfile.js');
        echo file_exists(BP . '/Gruntfile.js') ? "Created " . BP . "/Gruntfile.js \n" : '';
    }
    if (!file_exists(BP . '/package.json')) {
        copy(BP . '/dev/tools/Magento/Tools/Webdev/package.json.example', BP . '/package.json');
        echo file_exists(BP . '/package.json') ? "Created " . BP . "/package.json \n" : '';
    }
    $cmdSeparator = (substr(php_uname(), 0, 7) == "Windows") ? ' & ': ' && ';
    $cmd = 'cd ' . BP . $cmdSeparator . 'npm install' . $cmdSeparator .'grunt --no-color refresh';
    passthru($cmd);
    exit(0);
}
$locale = 'en_US';
if (isset($options['locale'])) {
    $locale =  $options['locale'];
    if (!preg_match('/^[a-z]{2}_[A-Z]{2}$/', $locale)) {
        echo USAGE;
        exit(1);
    }
}
$area = 'frontend';
if (isset($options['area'])) {
    $area = $options['area'];
}
$theme = 'Magento/blank';
if (isset($options['theme'])) {
    $theme =  $options['theme'];
}
$files = ['css/styles-m'];
if (isset($options['files'])) {
    $files = explode(',', $options['files']);
}
$verbosity = \Magento\Tools\View\Deployer\Log::ERROR;
if (isset($options['verbose'])) {
    $verbosity = 0 === (int)$options['verbose'] ? \Magento\Tools\View\Deployer\Log::SILENT
        : \Magento\Tools\View\Deployer\Log::ERROR | \Magento\Tools\View\Deployer\Log::DEBUG;
}

// run the deployment logic
$omFactory = \Magento\Framework\App\Bootstrap::createObjectManagerFactory(BP, []);
$objectManager = $omFactory->create(
    [\Magento\Framework\App\State::PARAM_MODE => \Magento\Framework\App\State::MODE_DEFAULT]
);
$logger = new \Magento\Tools\View\Deployer\Log($verbosity);
/** @var \Magento\Tools\View\Deployer $deployer */
$collector = $objectManager->create(
    'Magento\Tools\Webdev\Collector',
    ['logger' => $logger,]
);
$collector->tree($omFactory, $locale, $area, $theme, $files);
exit(0);
