<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Model;

use Magento\Payment\Model\MethodInterface;
use Magento\Payment\Gateway\CommandExecutorInterface;

/**
 * Interface VaultPaymentInterface
 * @api
 */
interface VaultPaymentInterface extends MethodInterface, CommandExecutorInterface
{
    const CODE = 'vault';

    const VAULT_TOKEN_COMMAND = 'vault_token';

    const VAULT_TOKEN_LIST_COMMAND = 'vault_token_list';

    const VAULT_AUTHORIZE_COMMAND = 'vault_authorize';

    const VAULT_CAPTURE_COMMAND = 'vault_capture';

    /**
     * @param string $paymentCode
     * @param null $storeId
     *
     * @return bool
     */
    public function isActiveForPayment($paymentCode, $storeId = null);

    /**
     * @param null $storeId
     *
     * @return string|null
     */
    public function getProviderCode($storeId = null);
}
