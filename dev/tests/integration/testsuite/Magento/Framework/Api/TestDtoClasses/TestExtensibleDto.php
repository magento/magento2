<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Api\TestDtoClasses;

class TestExtensibleDto implements TestExtensibleDtoInterface
{
    /**
     * @var string
     */
    private $paramOne;

    /**
     * @var string
     */
    private $paramTwo;

    /**
     * @var TestExtensibleDtoExtensionInterface
     */
    private $extensionAttributes;

    /**
     * @param string $paramOne
     * @param string $paramTwo
     * @param TestExtensibleDtoExtensionInterface $extensionAttributes
     */
    public function __construct(
        string $paramOne,
        string $paramTwo,
        TestExtensibleDtoExtensionInterface $extensionAttributes
    ) {
        $this->paramOne = $paramOne;
        $this->paramTwo = $paramTwo;
        $this->extensionAttributes = $extensionAttributes;
    }

    /**
     * @return string
     */
    public function getParamOne(): string
    {
        return $this->paramOne;
    }

    /**
     * @return string
     */
    public function getParamTwo(): string
    {
        return $this->paramTwo;
    }

    /**
     * @return TestExtensibleDtoExtensionInterface
     */
    public function getExtensionAttributes(): TestExtensibleDtoExtensionInterface
    {
        return $this->extensionAttributes;
    }
}
