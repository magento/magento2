<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Api\TestDtoClasses;

use Magento\Framework\Api\AbstractExtensibleObject;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;

class TestDtoWithCustomAttributes extends AbstractExtensibleObject
{
    /**
     * @var int
     */
    private $paramOne;

    /**
     * @var float
     */
    private $paramTwo;

    /**
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $attributeValueFactory
     * @param int $paramOne
     * @param float $paramTwo
     * @param array $data
     */
    public function __construct(
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $attributeValueFactory,
        int $paramOne,
        float $paramTwo,
        $data = []
    ) {
        parent::__construct($extensionFactory, $attributeValueFactory, $data);
        $this->paramOne = $paramOne;
        $this->paramTwo = $paramTwo;
    }

    /**
     * @return string[]
     */
    public function getCustomAttributesCodes()
    {
        return ['my_custom_attribute'];
    }

    /**
     * @return int
     */
    public function getParamOne(): int
    {
        return $this->paramOne;
    }

    /**
     * @return float
     */
    public function getParamTwo(): float
    {
        return $this->paramTwo;
    }
}
