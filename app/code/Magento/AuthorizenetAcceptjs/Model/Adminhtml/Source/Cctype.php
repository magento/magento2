<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Model\Adminhtml\Source;

use Magento\Payment\Model\Source\Cctype as PaymentCctype;

/**
 * Authorize.net Payment CC Types Source Model
 *
 * @deprecated Starting from Magento 2.2.11 Authorize.net payment method core integration is deprecated in favor of
 * official payment integration available on the marketplace
 */
class Cctype extends PaymentCctype
{
    /**
     * @inheritdoc
     */
    public function getAllowedTypes()
    {
        return ['VI', 'MC', 'AE', 'DI', 'JCB', 'DN'];
    }
}
