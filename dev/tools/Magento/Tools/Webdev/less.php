<?php
/**
 * A script for static view files partial pre-processing (when application in developer mode) and publication
 *
 * This tool is quite similar to deploy one (dev/tools/Magento/Tools/View/deploy.php), except this one perform only
 * less files pre-processing and publishing within particular area, locale, theme and files.
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\Framework\App\State;
use Magento\Tools\View\Deployer\Log;

require __DIR__ . '/../../../bootstrap.php';


try {
    $opt = new Zend_Console_Getopt(
        [
            'locale=s'  => 'locale, default: en_US',
            'area=s'    => 'area, one of (frontend|adminhtml|doc), default: frontend',
            'theme=s'   => 'theme in format Vendor/theme, default: Magento/blank',
            'files=s'   => 'files to pre-process (accept more than one file type as comma-separate values), default: css/styles-m',
            'setup'     => 'perform setup actions',
            'help|h'    => 'show help',
            'verbose|v' => 'provide extra output',
        ]
    );

    $opt->parse();

    // Parse and validate all options

    if ($opt->getOption('help')) {
        echo $opt->getUsageMessage();
        exit(0);
    }

    if ($opt->getOption('setup')) {
        echo "Setting up... \n";

        if (!file_exists(BP . '/Gruntfile.js')) {
            copy(BP . '/dev/tools/Magento/Tools/Webdev/Gruntfile.js.example', BP . '/Gruntfile.js');
            echo file_exists(BP . '/Gruntfile.js') ? "Created " . BP . "/Gruntfile.js \n" : '';
        }

        if (!file_exists(BP . '/package.json')) {
            copy(BP . '/dev/tools/Magento/Tools/Webdev/package.json.example', BP . '/package.json');
            echo file_exists(BP . '/package.json') ? "Created " . BP . "/package.json \n" : '';
        }

        $cmdSeparator = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? ' & ' : ' && ';

        $cmd = 'cd ' . BP . $cmdSeparator . 'npm install' . $cmdSeparator . 'grunt --no-color refresh';

        passthru($cmd);
        exit(0);
    }

    $locale = $opt->getOption('locale') ?: 'en_US';

    if (!preg_match('/^[a-z]{2}_[A-Z]{2}$/', $locale)) {
        throw new \Zend_Console_Getopt_Exception('Invalid locale format');
    }

    $area  = $opt->getOption('area') ?: 'frontend';
    $theme = $opt->getOption('theme') ?: 'Magento/blank';

    if (isset($options['theme'])) {
        $theme = $options['theme'];
    }

    $files = explode(',', $opt->getOption('files') ?: 'css/styles-m');


    if ($opt->getOption('verbose')) {
        $verbosity = Log::ERROR | Log::DEBUG;
    } else {
        $verbosity = Log::ERROR;
    }

    // Run actual application logic:

    $bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $_SERVER);

    // Initialize object manager
    $magentoObjectManagerFactory = \Magento\Framework\App\Bootstrap::createObjectManagerFactory(BP, $_SERVER);
    $magentoObjectManagerFactory->create($_SERVER);

    $objectManager = $magentoObjectManagerFactory->create([State::PARAM_MODE => State::MODE_DEFAULT]);

    $logger = new Log($verbosity);

    /** @var \Magento\Tools\Webdev\Collector $collector */
    $collector = $objectManager->create('Magento\Tools\Webdev\Collector', ['logger' => $logger]);

    $collector->tree($magentoObjectManagerFactory, $locale, $area, $theme, $files);

} catch (Zend_Console_Getopt_Exception $e) {
    echo $e->getUsageMessage();
    echo 'Please, use quotes(") for wrapping strings.' . "\n";
    exit(1);
} catch (Exception $e) {
    fwrite(STDERR, "Execution failed with exception: " . $e->getMessage());
    throw($e);
}
