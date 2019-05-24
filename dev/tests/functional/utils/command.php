<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
// phpcs:ignore Magento2.Security.IncludeFile
include __DIR__ . '/authenticate.php';
// phpcs:ignore Magento2.Security.IncludeFile
require_once __DIR__ . '/../../../../app/bootstrap.php';

use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;

// phpcs:ignore Magento2.Security.Superglobal
if (!empty($_POST['token']) && !empty($_POST['command'])) {
    // phpcs:ignore Magento2.Security.Superglobal
    if (authenticate(urldecode($_POST['token']))) {
        // phpcs:ignore Magento2.Security.Superglobal
        $command = urldecode($_POST['command']);
        // phpcs:ignore Magento2.Security.Superglobal
        $magentoObjectManagerFactory = \Magento\Framework\App\Bootstrap::createObjectManagerFactory(BP, $_SERVER);
        // phpcs:ignore Magento2.Security.Superglobal
        $magentoObjectManager = $magentoObjectManagerFactory->create($_SERVER);
        $cli = $magentoObjectManager->create(\Magento\Framework\Console\Cli::class);
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $input = new StringInput(escapeshellcmd($command));
        $input->setInteractive(false);
        $output = new NullOutput();
        $cli->doRun($input, $output);
    } else {
        // phpcs:ignore Magento2.Security.LanguageConstruct
        echo "Command not unauthorized.";
    }
} else {
    // phpcs:ignore Magento2.Security.LanguageConstruct
    echo "'token' or 'command' parameter is not set.";
}
