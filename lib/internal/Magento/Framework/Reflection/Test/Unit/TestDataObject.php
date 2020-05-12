<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Reflection\Test\Unit;

use Magento\Framework\Api\ExtensionAttributesInterface;

class TestDataObject implements TestDataInterface
{
    private $extensionAttributes;

    /**
     * TestDataObject constructor.
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
     * @return ExtensionAttributesInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->extensionAttributes;
    }
}
