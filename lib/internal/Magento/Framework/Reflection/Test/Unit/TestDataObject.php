<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Reflection\Test\Unit;

class TestDataObject implements TestDataInterface
{
    private $extensionAttributes;

    /**
     * TestDataObject constructor.
     *
     * @param null $extensionAttributes
     */
    public function __construct($extensionAttributes = null)
    {
        $this->extensionAttributes = $extensionAttributes;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return '1';
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return 'someAddress';
    }

    /**
     * @return string
     */
    public function isDefaultShipping()
    {
        return 'true';
    }

    /**
     * @return string
     */
    public function isRequiredBilling()
    {
        return 'false';
    }

    /**
     * @return \Magento\Framework\Api\ExtensionAttributesInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->extensionAttributes;
    }
}
