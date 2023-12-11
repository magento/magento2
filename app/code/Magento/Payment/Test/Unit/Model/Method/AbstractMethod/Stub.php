<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Payment\Test\Unit\Model\Method\AbstractMethod;

use Magento\Payment\Model\Method\AbstractMethod;

/**
 * Class Stub
 *
 * Stub for \Magento\Payment\Model\Method\AbstractMethod
 */
class Stub extends AbstractMethod
{
    const STUB_CODE = 'stub-code';

    /**
     * @return string
     */
    public function getCode()
    {
        return static::STUB_CODE;
    }
}
