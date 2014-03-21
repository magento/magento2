<?php
/**
 * A command line tool that pre-populates static view files into public directory.
 * In the production mode paths and URLs are to be composed without the filesystem lookup.
 *
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
 * @category    Magento
 * @package     Tools
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require __DIR__ . '/../../../../../app/bootstrap.php';
\Magento\Autoload\IncludePath::addIncludePath(__DIR__ . '/../../../');

/**
 * Command line usage help
 */
define(
    'SYNOPSIS',
<<<USAGE
Usage: php -f generator.php -- [--source <dir>] [--destination <dir>] [--dry-run]
       php -f generator.php -- --help

  --source <dir>      Root directory to start search of static view files from.
                      If omitted, the application root directory is used.

  --destination <dir> Directory to copy files to.
                      If omitted, public location of static view files is used.

  --dry-run           Do not create directories and files in a destination path.

  --help              Print this usage information.

USAGE
);

$logWriter = new \Zend_Log_Writer_Stream('php://output');
$logWriter->setFormatter(new \Zend_Log_Formatter_Simple('%message%' . PHP_EOL));
$logger = new \Zend_Log($logWriter);

$options = getopt('', array('help', 'dry-run', 'source:', 'destination:'));
if (isset($options['help'])) {
    $logger->log(SYNOPSIS, \Zend_Log::INFO);
    exit(0);
}

$logger->log('Deploying...', \Zend_Log::INFO);
try {

    $objectManagerFactory = new \Magento\App\ObjectManagerFactory();
    $objectManager = $objectManagerFactory->create(BP, $_SERVER);

    $config = $objectManager->create(
        'Magento\Tools\View\Generator\Config',
        array('cmdOptions' => $options, 'allowedFiles' => array('.htaccess'))
    );
    $themes = $objectManager->create('Magento\Core\Model\Theme\Collection');
    $themes->setItemObjectClass('Magento\Tools\View\Generator\ThemeLight');
    $themes->addDefaultPattern('*');
    $fallbackFactory = $objectManager->create('Magento\View\Design\Fallback\Factory');
    $generator = $objectManager->create(
        'Magento\Tools\View\Generator\CopyRule',
        array('themes' => $themes, 'fallbackRule' => $fallbackFactory->createViewFileRule())
    );
    $copyRules = $generator->getCopyRules();
    $deployment = $objectManager->create(
        'Magento\Tools\View\Generator\ThemeDeployment',
        array(
            'destinationHomeDir' => $config->getDestinationDir(),
            'configPermitted' => __DIR__ . '/config/permitted.php',
            'configForbidden' => __DIR__ . '/config/forbidden.php',
            'isDryRun' => $config->isDryRun(),
            'preProcessor' => $objectManager->create('Magento\View\Asset\PreProcessor\Composite')
        )
    );
    $deployment->run($copyRules);
} catch (\Exception $e) {
    $logger->log('Error: ' . $e->getMessage(), \Zend_Log::ERR);
    exit(1);
}
$logger->log('Completed successfully.', \Zend_Log::INFO);
