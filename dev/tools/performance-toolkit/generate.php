<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

$applicationBaseDir = require_once __DIR__ . '/framework/bootstrap.php';

try {
    $totalStartTime = microtime(true);

    $shell = new Zend_Console_Getopt(
        [
            'profile-s' => 'Profile configuration file',
        ]
    );

    \Magento\ToolkitFramework\Helper\Cli::setOpt($shell);

    $args = $shell->getOptions();
    if (empty($args)) {
        echo $shell->getUsageMessage();
        exit(0);
    }

    $logWriter = new \Zend_Log_Writer_Stream('php://output');
    $logWriter->setFormatter(new \Zend_Log_Formatter_Simple('%message%' . PHP_EOL));
    $logger = new \Zend_Log($logWriter);

    $shell = new \Magento\Framework\Shell(new \Magento\Framework\Shell\CommandRenderer(), $logger);

    $application = new \Magento\ToolkitFramework\Application($applicationBaseDir, $shell, []);
    $application->bootstrap();
    $application->loadFixtures();

    $config = \Magento\ToolkitFramework\Config::getInstance();
    $config->loadConfig(\Magento\ToolkitFramework\Helper\Cli::getOption('profile'));

    echo 'Generating profile with following params:' . PHP_EOL;
    foreach ($application->getParamLabels() as $configKey => $label) {
        echo ' |- ' . $label . ': ' . $config->getValue($configKey) . PHP_EOL;
    }

    /** @var $config \Magento\Indexer\Model\Config */
    $config = $application->getObjectManager()->get('Magento\Indexer\Model\Config');
    $indexerListIds = $config->getIndexers();
    /** @var $indexerRegistry \Magento\Indexer\Model\IndexerRegistry */
    $indexerRegistry = $application->getObjectManager()->create('Magento\Indexer\Model\IndexerRegistry');
    $indexersState = [];
    foreach ($indexerListIds as $key => $indexerId) {
        $indexer = $indexerRegistry->get($indexerId['indexer_id']);
        $indexersState[$indexerId['indexer_id']] = $indexer->isScheduled();
        $indexer->setScheduled(true);
    }

    foreach ($application->getFixtures() as $fixture) {
        echo $fixture->getActionTitle() . '... ';
        $startTime = microtime(true);
        $fixture->execute();
        $endTime = microtime(true);
        $resultTime = $endTime - $startTime;
        echo ' done in ' . gmdate('H:i:s', $resultTime) . PHP_EOL;
    }

    foreach ($indexerListIds as $indexerId) {
        /** @var $indexer \Magento\Indexer\Model\Indexer */
        $indexer = $indexerRegistry->get($indexerId['indexer_id']);
        $indexer->setScheduled($indexersState[$indexerId['indexer_id']]);
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
