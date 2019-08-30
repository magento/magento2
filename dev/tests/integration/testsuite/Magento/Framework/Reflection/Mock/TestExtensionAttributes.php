<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Reflection\Mock;

use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\Api\ExtensionAttributesInterface;

class TestExtensionAttributes extends AbstractSimpleObject implements ExtensionAttributesInterface
{
    /**
     * @param string $value
     * @return void
     */
    public function setAttribute1(string $value): void
    {
        $this->setData('attribute1', $value);
    }

    /**
     * @param string $value
     * @return void
     */
    public function setAttribute2(string $value): void
    {
        $this->setData('attribute2', $value);
    }

    /**
     * @return string
     */
    public function getAttribute1(): string
    {
        return (string) $this->_get('attribute1');
    }

    /**
     * @return string
     */
    public function getAttribute2(): string
    {
        return (string) $this->_get('attribute2');
    }
}
