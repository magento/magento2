<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// phpcs:ignore Magento2.Security.IncludeFile
require_once __DIR__ . '/../../../../app/bootstrap.php';

use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;

// phpcs:ignore Magento2.Security.Superglobal
if (isset($_GET['command'])) {
    // phpcs:ignore Magento2.Security.Superglobal
    $command = urldecode($_GET['command']);
    // phpcs:ignore Magento2.Security.Superglobal
    $magentoObjectManagerFactory = \Magento\Framework\App\Bootstrap::createObjectManagerFactory(BP, $_SERVER);
    // phpcs:ignore Magento2.Security.Superglobal
    $magentoObjectManager = $magentoObjectManagerFactory->create($_SERVER);
    $cli = $magentoObjectManager->create(\Magento\Framework\Console\Cli::class);
    $input = new StringInput($command);
    $input->setInteractive(false);
    $output = new NullOutput();
    $cli->doRun($input, $output);
} else {
    throw new \InvalidArgumentException("Command GET parameter is not set.");
}
