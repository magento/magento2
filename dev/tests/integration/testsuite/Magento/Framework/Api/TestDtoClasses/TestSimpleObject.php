<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Api\TestDtoClasses;

use Magento\Framework\Api\AbstractSimpleObject;

class TestSimpleObject extends AbstractSimpleObject
{
    /**
     * @return string
     */
    public function getParamOne(): string
    {
        return (string) $this->_get('param_one');
    }

    /**
     * @return string
     */
    public function getParamTwo(): string
    {
        return (string) $this->_get('param_two');
    }

    /**
     * @return string
     */
    public function getParamThree(): string
    {
        return (string) $this->_get('param_two');
    }
}
