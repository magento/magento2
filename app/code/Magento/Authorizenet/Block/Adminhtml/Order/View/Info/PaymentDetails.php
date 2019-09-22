<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Authorizenet\Block\Adminhtml\Order\View\Info;

use Magento\Framework\Phrase;
use Magento\Payment\Block\ConfigurableInfo;

/**
 * Payment information block for Authorize.net payment method
 * @deprecated 2.3.1 Authorize.net is removing all support for this payment method
 */
class PaymentDetails extends ConfigurableInfo
{
    /**
     * Returns localized label for payment info block
     *
     * @param string $field
     * @return string | Phrase
     */
    protected function getLabel($field)
    {
        return __($field);
    }
}
