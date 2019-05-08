<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
include __DIR__ . '/authenticate.php';
require_once __DIR__ . '/../../../../app/bootstrap.php';

use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;

if (!empty($_POST['token']) && !empty($_POST['command'])) {
    if (authenticate(urldecode($_POST['token']))) {
        $command = urldecode($_POST['command']);
        $magentoObjectManagerFactory = \Magento\Framework\App\Bootstrap::createObjectManagerFactory(BP, $_SERVER);
        $magentoObjectManager = $magentoObjectManagerFactory->create($_SERVER);
        $cli = $magentoObjectManager->create(\Magento\Framework\Console\Cli::class);
        $input = new StringInput($command);
        $input->setInteractive(false);
        $output = new NullOutput();
        $cli->doRun($input, $output);
    } else {
        echo "Command not unauthorized.";
    }
} else {
    echo "'token' or 'command' parameter is not set.";
}
