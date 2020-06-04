<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Model\Payment\Method\Billing;

use Magento\Paypal\Model\Payment\Method\Billing\AbstractAgreement;

class AbstractAgreementStub extends AbstractAgreement
{
    const STUB_CODE = 'stub-code';

    /**
     * @return string
     */
    public function getCode()
    {
        return static::STUB_CODE;
    }

    /**
     * @param object $quote
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _isAvailable($quote)
    {
        return false;
    }
}
