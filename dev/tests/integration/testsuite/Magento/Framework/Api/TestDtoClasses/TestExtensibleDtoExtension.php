<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Api\TestDtoClasses;

use Magento\Framework\Api\AbstractSimpleObject;

class TestExtensibleDtoExtension extends AbstractSimpleObject implements TestExtensibleDtoExtensionInterface
{
    /**
     * @return string
     */
    public function getAdditionalValue()
    {
        return (string) $this->_get('additional_value');
    }
}
