<?php
require_once dirname(__FILE__) . '/' . 'bootstrap.php';

$magentoObjectManagerFactory = \Magento\Framework\App\Bootstrap::createObjectManagerFactory(BP, $_SERVER);
$magentoObjectManager = $magentoObjectManagerFactory->create($_SERVER);

$logger = $objectManager->create(\Magento\Mtf\System\Logger::class);
$logger->setLogDirectoryPath(__DIR__ . '/../../../../var/log');
$logger->log('I am in command.php before if. ', 'debug.log');

if (isset($_GET['command'])) {
    $logger->log('I am in command.php in if. ', 'debug.log');
    $command = urldecode($_GET['command']);
    exec('php -f ../../../../bin/magento ' . $command);
    $logger->log('I am in command.php after cache flush. ', 'debug.log');
} else {
    throw new \InvalidArgumentException("Command GET parameter is not set.");
}