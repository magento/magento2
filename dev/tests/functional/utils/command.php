<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require_once __DIR__ . '/../../../../app/bootstrap.php';

use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;

if (isset($_GET['command'])) {
    $command = urldecode($_GET['command']);
    $magentoObjectManagerFactory = \Magento\Framework\App\Bootstrap::createObjectManagerFactory(BP, $_SERVER);
    $magentoObjectManager = $magentoObjectManagerFactory->create($_SERVER);
    $cli = $magentoObjectManager->create(\Magento\Framework\Console\Cli::class);
    $input = new StringInput($command);
    $input->setInteractive(false);
    $output = new NullOutput();
    $cli->doRun($input, $output);
} else {
    throw new \InvalidArgumentException("Command GET parameter is not set.");
}
