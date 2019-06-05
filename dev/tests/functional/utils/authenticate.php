<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Check if token passed in is a valid auth token.
 *
 * @param string $token
 * @return bool
 */
function authenticate($token)
{
    require_once __DIR__ . '/../../../../app/bootstrap.php';

    $magentoObjectManagerFactory = \Magento\Framework\App\Bootstrap::createObjectManagerFactory(BP, $_SERVER);
    $magentoObjectManager = $magentoObjectManagerFactory->create($_SERVER);
    $tokenModel = $magentoObjectManager->get(\Magento\Integration\Model\Oauth\Token::class);

    $tokenPassedIn = $token;
    // Token returned will be null if the token we passed in is invalid
    $tokenFromMagento = $tokenModel->loadByToken($tokenPassedIn)->getToken();
    if (!empty($tokenFromMagento) && ($tokenFromMagento == $tokenPassedIn)) {
        return true;
    } else {
        return false;
    }
}
