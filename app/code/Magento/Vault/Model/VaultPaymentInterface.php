<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Model;

use Magento\Payment\Model\MethodInterface;

/**
 * Interface VaultPaymentInterface
 * @api
 */
interface VaultPaymentInterface extends MethodInterface
{
    const VAULT_AUTHORIZE_COMMAND = 'vault_authorize';

    const VAULT_SALE_COMMAND = 'vault_sale';

    const CAN_AUTHORIZE = 'can_authorize_vault';

    const CAN_CAPTURE = 'can_capture_vault';

    /**
     * @return string|null
     */
    public function getProviderCode();
}
