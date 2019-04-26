<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Api\TestDtoClasses;

class TestDto
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
     * @var string
     */
    private $paramThree;

    /**
     * @param int $paramOne
     * @param float $paramTwo
     * @param string $paramThree
     */
    public function __construct(
        int $paramOne,
        float $paramTwo,
        string $paramThree
    ) {
        $this->paramOne = $paramOne;
        $this->paramTwo = $paramTwo;
        $this->paramThree = $paramThree;
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

    /**
     * @return string
     */
    public function getParamThree(): string
    {
        return $this->paramThree;
    }
}
