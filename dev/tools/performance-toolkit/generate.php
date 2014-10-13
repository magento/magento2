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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$applicationBaseDir = require_once __DIR__ . '/framework/bootstrap.php';

try {
    $totalStartTime = microtime(true);

    $shell = new Zend_Console_Getopt(
        array(
            'profile-s' => 'Profile configuration file',
        )
    );

    \Magento\ToolkitFramework\Helper\Cli::setOpt($shell);

    $args = $shell->getOptions();
    if (empty($args)) {
        echo $shell->getUsageMessage();
        exit(0);
    }

    $config = \Magento\ToolkitFramework\Config::getInstance();
    $config->loadConfig(\Magento\ToolkitFramework\Helper\Cli::getOption('profile'));
    $config->loadLabels(__DIR__ . '/framework/labels.xml');

    $labels = $config->getLabels();

    echo 'Generating profile with following params:' . PHP_EOL;
    foreach ($labels as $configKey => $label) {
        echo ' |- ' . $label . ': ' . $config->getValue($configKey) . PHP_EOL;
    }

    $files = \Magento\ToolkitFramework\FixtureSet::getInstance()->getFixtures();

    $logWriter = new \Zend_Log_Writer_Stream('php://output');
    $logWriter->setFormatter(new \Zend_Log_Formatter_Simple('%message%' . PHP_EOL));
    $logger = new \Zend_Log($logWriter);

    $shell = new \Magento\Framework\Shell(new \Magento\Framework\Shell\CommandRenderer(), $logger);

    $application = new \Magento\ToolkitFramework\Application($applicationBaseDir, $shell);
    $application->bootstrap();

    foreach ($files as $fixture) {
        echo $fixture['action'] . '... ';
        $startTime = microtime(true);
        $application->applyFixture(__DIR__ . '/fixtures/' . $fixture['file']);
        $endTime = microtime(true);
        $resultTime = $endTime - $startTime;
        echo ' done in ' . gmdate('H:i:s', $resultTime) . PHP_EOL;
    }

    $application->reindex();
    $totalEndTime = microtime(true);
    $totalResultTime = $totalEndTime - $totalStartTime;

    echo 'Total execution time: ' . gmdate('H:i:s', $totalResultTime) . PHP_EOL;
} catch (\Zend_Console_Getopt_Exception $e) {
    fwrite(STDERR, $e->getMessage() . "\n\n" . $e->getUsageMessage() . "\n");
    exit(1);
} catch (\Exception $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
}
