<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require_once __DIR__ . '/../../../../app/bootstrap.php';

use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;

if (!empty($_POST['token']) && !empty($_POST['command'])) {
    $magentoObjectManagerFactory = \Magento\Framework\App\Bootstrap::createObjectManagerFactory(BP, $_SERVER);
    $magentoObjectManager = $magentoObjectManagerFactory->create($_SERVER);
    $tokenModel = $magentoObjectManager->get(\Magento\Integration\Model\Oauth\Token::class);

    $tokenPassedIn = urldecode($_POST['token']);
    $command = urldecode($_POST['command']);

    // Token returned will be null if the token we passed in is invalid
    $tokenFromMagento = $tokenModel->loadByToken($tokenPassedIn)->getToken();
    if (!empty($tokenFromMagento) && ($tokenFromMagento == $tokenPassedIn)) {
        $cli = $magentoObjectManager->create(\Magento\Framework\Console\Cli::class);
        $input = new StringInput(escapeshellcmd($command));
        $input->setInteractive(false);
        $output = new NullOutput();
        $cli->doRun($input, $output);
    } else {
        throw new \Exception("Command not unauthorized.");
    }
} else {
    throw new \InvalidArgumentException("Command POST parameters are not set.");
}
