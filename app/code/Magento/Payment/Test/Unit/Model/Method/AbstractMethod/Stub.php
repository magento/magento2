<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Test\Unit\Model\Method\AbstractMethod;

/**
 * Class Stub
 *
 * Stub for \Magento\Payment\Model\Method\AbstractMethod
 */
class Stub extends \Magento\Payment\Model\Method\AbstractMethod
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
