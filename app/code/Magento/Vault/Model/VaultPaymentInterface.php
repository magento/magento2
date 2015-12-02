<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Model;

use Magento\Payment\Model\MethodInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Payment\Gateway\CommandExecuteInterface;

/**
 * Interface VaultPaymentInterface
 */
interface VaultPaymentInterface extends MethodInterface, CommandExecuteInterface
{
    const VAULT_TOKEN_COMMAND = 'vault_token';

    const VAULT_TOKEN_LIST_COMMAND = 'vault_token_list';

    const VAULT_AUTHORIZE_COMMAND = 'vault_authorize';

    const VAULT_CAPTURE_COMMAND = 'vault_capture';
}
