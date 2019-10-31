<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Dto\Mock;

use Magento\Framework\Api\AbstractSimpleObject;

class TestSimpleObject extends AbstractSimpleObject
{
    /**
     * @return string
     */
    public function getProp1(): string
    {
        return (string) $this->_get('prop1');
    }

    /**
     * @return string
     */
    public function getProp2(): string
    {
        return (string) $this->_get('prop2');
    }
}
