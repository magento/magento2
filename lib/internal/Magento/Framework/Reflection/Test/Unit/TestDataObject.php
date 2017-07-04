<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Reflection\Test\Unit;

class TestDataObject implements TestDataInterface
{
    private $extensionAttributes;

    public function __construct($extensionAttributes = null)
    {
        $this->extensionAttributes = $extensionAttributes;
    }

    public function getId()
    {
        return '1';
    }

    public function getAddress()
    {
        return 'someAddress';
    }

    public function isDefaultShipping()
    {
        return 'true';
    }

    public function isRequiredBilling()
    {
        return 'false';
    }

    public function getExtensionAttributes()
    {
        return $this->extensionAttributes;
    }
}
