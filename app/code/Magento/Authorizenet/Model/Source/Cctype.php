<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Authorizenet\Model\Source;

use Magento\Payment\Model\Source\Cctype as PaymentCctype;

/**
 * Authorize.net Payment CC Types Source Model
 * @deprecated 100.3.1 Authorize.net is removing all support for this payment method
 */
class Cctype extends PaymentCctype
{
    /**
     * Return all supported credit card types
     *
     * @return string[]
     */
    public function getAllowedTypes()
    {
        return ['VI', 'MC', 'AE', 'DI', 'JCB', 'DN'];
    }
}
