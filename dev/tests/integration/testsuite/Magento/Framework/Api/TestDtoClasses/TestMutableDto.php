<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Api\TestDtoClasses;

class TestMutableDto
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
     * @return string
     */
    public function getParamOne(): string
    {
        return $this->paramOne;
    }

    /**
     * @param string $paramOne
     */
    public function setParamOne(string $paramOne): void
    {
        $this->paramOne = $paramOne;
    }

    /**
     * @return string
     */
    public function getParamTwo(): string
    {
        return $this->paramTwo;
    }

    /**
     * @param string $paramTwo
     */
    public function setParamTwo(string $paramTwo): void
    {
        $this->paramTwo = $paramTwo;
    }
}
